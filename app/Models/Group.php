<?php

namespace App\Models;

use App\Events\GroupDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    const DEFAULT_GROUP = 1;
    const GROUP_IMAGE_PATH = 'images/group/';

    use HasFactory;

    /**
     * Defines the Group to GroupMember (User) relationship.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members');
    }

    /**
     * Defines the Group to Expense relationship.
     */
    public function expenses()
    {
        return $this->belongsToMany(Expense::class, 'expense_groups');
    }

    /**
     * Returns the URL to the group's image.
     */
    public function getGroupImageUrlAttribute()
    {
        if ($this->img_file === null) {
            return null;
        }

        return asset(self::GROUP_IMAGE_PATH . $this->img_file);
    }

    /**
     * Returns the first letter of the group's name.
     */
    public function getInitial()
    {
        return strtoupper($this->name[0]);
    }

    /**
     * Creates a default image for the group with its first letter.
     */
    public function createDefaultGroupImage()
    {
        $filename = time().'-group-image-' . $this->id . '.png';

        $image_path = public_path(self::GROUP_IMAGE_PATH . $filename);

        $initial = $this->getInitial();

        // Define a background color and text color for the avatar
        $bg_colour = '#'.substr(md5($this->name), 0, 6); // Unique colour based on group name
        $text_colour = '#ffffff'; // White text colour

        // Create an image with the initial and colors
        $image = imagecreate(200, 200);
        $bg = imagecolorallocate($image, hexdec(substr($bg_colour, 1, 2)), hexdec(substr($bg_colour, 3, 2)), hexdec(substr($bg_colour, 5, 2)));
        $text = imagecolorallocate($image, hexdec(substr($text_colour, 1, 2)), hexdec(substr($text_colour, 3, 2)), hexdec(substr($text_colour, 5, 2)));
        imagefill($image, 0, 0, $bg);
        imagettftext($image, 100, 0, 50, 150, $text, public_path('fonts/ARIAL.TTF'), $initial);

        // Save the image file
        imagepng($image, $image_path);
        imagedestroy($image);

        $this->img_file = $filename;
        $this->save();

        return asset($image_path);
    }

    /**
     * Deletes the group image from the server.
     */
    public function deleteGroupImage()
    {
        $image_path = public_path(self::GROUP_IMAGE_PATH . $this->img_file);

        // Delete the image
        if ($this->img_file && file_exists($image_path)) {
            unlink($image_path);
        }
    }

    protected $fillable = [
        'name',
        'img_file',
        'owner',
    ];

    protected $casts = [
        'owner' => 'int',
    ];

    protected $dispatchesEvents = [
        'deleting' => GroupDeleting::class,
    ];
}

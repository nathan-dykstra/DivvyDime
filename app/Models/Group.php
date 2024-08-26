<?php

namespace App\Models;

use App\Events\GroupDeleting;
use App\Traits\DefaultImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    const DEFAULT_GROUP = 1;
    const GROUP_IMAGE_PATH = 'images/group/';

    use HasFactory;
    use DefaultImage;

    /**
     * Defines the Group to GroupMember (User) relationship.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')->withPivot('is_active');
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
     * Creates a default image for the group with its first letter.
     */
    public function createDefaultGroupImage()
    {
        $filename = time().'-group-image-' . $this->id . '.png';

        $asset = $this->createDefaultImage(self::GROUP_IMAGE_PATH, $filename, $this->name);

        // Save the filename in the database
        $this->img_file = $filename;
        $this->save();

        return $asset;
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseImage extends Model
{
    use HasFactory;

    const EXPENSE_IMAGE_PATH = 'images/expense/';

    /**
     * Defines the ExpenseImage to Expense relationship.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Returns the URL to the user's profile image.
     */
    public function getExpenseImageUrlAttribute(): ?string
    {
        if ($this->img_file === null) {
            return null;
        }

        return asset(self::EXPENSE_IMAGE_PATH . $this->img_file);
    }

    /**
     * Deletes the expense image from the server.
     */
    public function deleteExpenseImage()
    {
        $image_path = public_path(self::EXPENSE_IMAGE_PATH . $this->img_file);

        // Delete the image
        if ($this->img_file && file_exists($image_path)) {
            unlink($image_path);
        }
    }

    protected $fillable = [
        'expense_id',
        'img_file',
    ];

    protected $casts = [
        'expense_id' => 'int',
    ];
}

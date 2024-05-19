<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseImage;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class ImageController extends Controller
{
    const IMAGE_BASE_PATH = 'images/';
    const PROFILE_IMAGE_PATH = 'images/profile/';
    const EXPENSE_IMAGE_PATH = 'images/expense/';
    const GROUP_IMAGE_PATH = 'images/group/';

    /**
     * 
     */
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'file' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $current_user = $request->user();

        $image = $request->file('file');
        $filename = time().'-profile-image-' . $current_user->id . '.' . $image->extension();

        $current_image_path = public_path(self::PROFILE_IMAGE_PATH . $current_user->profile_img_file);
        if (file_exists($current_image_path)) {
            unlink($current_image_path);
        }

        // Store the new profile image
        $image->move(public_path(self::PROFILE_IMAGE_PATH), $filename);

        // Save image filename in the database
        $current_user->profile_img_file = $filename;
        $current_user->save();

        Session::flash('status', 'profile-image-uploaded');

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'redirect' => route('profile.edit'),
        ]);
    }

    /**
     * 
     */
    public function deleteProfileImage(Request $request)
    {
        $current_user = $request->user();

        $current_user->deleteProfileImage();

        // Generate a default image with the user's initials
        $current_user->createDefaultProfileImage();

        return Redirect::route('profile.edit')->with('status', 'profile-image-deleted');
    }

    /**
     * 
     */
    public function uploadExpenseImages(Request $request, Expense $expense)
    {
        $request->validate([
            'file' => ['required', 'array', 'max:5'],
            'file.*' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $current_image_count = $expense->images->count();

        if ($current_image_count >= Expense::MAX_IMAGES_ALLOWED) {
            Session::flash('status', 'max-images-reached');

            return response()->json([
                'success' => true,
                'message' => 'Maximum number of image uploads reached',
                'redirect' => route('expenses.show', $expense->id),
            ]);
        }

        $image_count = $current_image_count + 1;
        foreach ($request->file('file') as $image) {
            $filename = time().'-expense-image-' . $image_count . '.' . $image->extension();

            // Store the new expense image
            $image->move(public_path(self::EXPENSE_IMAGE_PATH), $filename);

            // Save image filename in the database
            ExpenseImage::create([
                'expense_id' => $expense->id,
                'img_file' => $filename,
            ]);

            $image_count++;
        }

        // Update the expense's updated_at timestamp if images were added
        if ($image_count > $current_image_count) {
            $expense->touch();
        }

        Session::flash('status', 'expense-images-uploaded');

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'redirect' => route('expenses.show', $expense->id),
        ]);
    }

    /**
     * 
     */
    public function deleteExpenseImage(Request $request)
    {

    }

    /**
     * 
     */
    public function uploadGroupImage(Request $request, Group $group)
    {
        $request->validate([
            'file' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $image = $request->file('file');
        $filename = time().'-group-image-' . $group->id . '.' . $image->extension();

        $current_image_path = public_path(self::GROUP_IMAGE_PATH . $group->img_file);
        if (file_exists($current_image_path)) {
            unlink($current_image_path);
        }

        // Store the new profile image
        $image->move(public_path(self::GROUP_IMAGE_PATH), $filename);

        // Save image filename in the database
        $group->img_file = $filename;
        $group->save();

        Session::flash('status', 'group-image-uploaded');

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'redirect' => route('groups.settings', $group),
        ]);
    }

    /**
     * 
     */
    public function deleteGroupImage(Request $request, Group $group)
    {
        $group->deleteGroupImage();

        // Generate a default image with the user's initials
        $group->createDefaultGroupImage();

        return Redirect::route('groups.settings', $group->id)->with('status', 'group-image-deleted');
    }
}

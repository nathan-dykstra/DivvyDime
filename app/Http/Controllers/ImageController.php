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
     * Uploads a new profile image.
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
            'redirect' => route('profile.edit'),
        ]);
    }

    /**
     * Deletes the profile image from the server and database, and replaces with a default image.
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
     * Uploads new expense images (up to the limit specified in the Expense model)
     */
    public function uploadExpenseImages(Request $request, Expense $expense)
    {
        $request->validate([
            'file' => ['required', 'array', 'max:5'],
            'file.*' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $current_expense_images_count = $expense->images->count();
        $images = $request->file('file');
        $expense_images_count = $current_expense_images_count + 1;

        foreach ($images as $image) {
            $filename = time().'-expense-image-'.$expense->id.'-'.$expense_images_count.'.'.$image->extension();

            // Store the new expense image
            $image->move(public_path(self::EXPENSE_IMAGE_PATH), $filename);

            // Save image filename in the database
            ExpenseImage::create([
                'expense_id' => $expense->id,
                'img_file' => $filename,
            ]);

            if ($expense_images_count >= Expense::MAX_IMAGES_ALLOWED && count($images) > Expense::MAX_IMAGES_ALLOWED - $current_expense_images_count) {
                // Update the expense's updated_at timestamp
                $expense->touch();

                Session::flash('status', 'max-images-reached');

                return response()->json([
                    'success' => true,
                    'redirect' => route('expenses.show', $expense->id),
                ]);
            }

            $expense_images_count++;
        }

        // Update the expense's updated_at timestamp
        $expense->touch();

        Session::flash('status', 'expense-images-uploaded');

        return response()->json([
            'success' => true,
            'redirect' => route('expenses.show', $expense->id),
        ]);
    }

    /**
     * Deletes the expense image from the server and database.
     */
    public function deleteExpenseImage(Request $request, $expense_image_id)
    {
        if ($expense_image_id) {
            $expense_image = ExpenseImage::find($expense_image_id);
            if ($expense_image) {
                $expense_id = $expense_image->expense_id;

                $expense_image->deleteExpenseImage();
                $expense_image->delete();

                $expense = Expense::find($expense_id);
                $expense->touch();

                return Redirect::route('expenses.show', $expense_id)->with('status', 'expense-image-deleted');
            }
        }

        return Redirect::back();
    }

    /**
     * Uploads a new group image.
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
            'redirect' => route('groups.settings', $group),
        ]);
    }

    /**
     * Deletes the group image from the server and database, and replaces with a default.
     */
    public function deleteGroupImage(Request $request, Group $group)
    {
        $group->deleteGroupImage();

        // Generate a default image with the user's initials
        $group->createDefaultGroupImage();

        return Redirect::route('groups.settings', $group->id)->with('status', 'group-image-deleted');
    }
}

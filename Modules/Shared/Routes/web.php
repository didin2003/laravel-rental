<?php

use Illuminate\Support\Facades\Route;
use Modules\Shared\Http\Controllers\SharedController;

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('dashboard', [SharedController::class, 'index'])->name('dashboard');

    // Fetch User Bookings // purpose ajax Reminder
    Route::post('user-reminders', [SharedController::class, 'userReminders'])->name('user.reminders');

    // Reminder
    Route::delete('skip-reminder/{reminder}', [SharedController::class, 'skipReminder'])->name('skip.reminder');
    Route::post('remind-later/{reminder}', [SharedController::class, 'remindLater'])->name('remind.later');

    Route::middleware(['role:super_admin|admin'])->group(function () {
        // Testimonial
        Route::get('manage-testimonials', [SharedController::class, 'manageTestimonials'])->name('manage.testimonials');
        Route::post('testimonials/toggle', [SharedController::class, 'testimonalsToggle'])->name('testimonials.toggle');
        Route::delete('delete-testimonial', [SharedController::class, 'deleteTestimonial'])->name('delete.testimonials');
        Route::delete('delete-multiple-testimonial', [SharedController::class, 'deleteMultipleTestimonial'])->name('delete.multiple.testimonials');
        Route::post('enable-multiple-testimonial', [SharedController::class, 'enableMultipleTestimonial'])->name('enable.multiple.testimonials');
        Route::post('disable-multiple-testimonial', [SharedController::class, 'disableMultipleTestimonial'])->name('disable.multiple.testimonials');
        Route::put('edit-testimonial/{testimonial}', [SharedController::class, 'editTestimonial'])->name('edit.testimonial');

        // FAQS
        Route::get('manage-faqs', [SharedController::class, 'manageFaqs'])->name('manage.faqs');
        Route::post('/add-faq', [SharedController::class, 'addFaq'])->name('add.faq');
        Route::post('/edit-faq/{faq}', [SharedController::class, 'editFaq'])->name('edit.faq');
        Route::post('/faq/toggle', [SharedController::class, 'toggleFaq'])->name('faqs.toggle');
        Route::delete('/delete-faq/{faq}', [SharedController::class, 'deleteFaq'])->name('delete.faq');
    });
    Route::post('add-testimonial/{property}', [SharedController::class, 'addTestimonial'])->name('add.testimonial');


});

<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword2fa;
use App\Livewire\Auth\ResetPasswordChanged;
use App\Livewire\Auth\ResetPasswordChangePassword;
use App\Livewire\Auth\ResetPasswordEnterEmail;
use App\Livewire\Homepage\FeedbackForms\Feedback;
use App\Livewire\Homepage\FeedbackForms\FeedbackFormManagement;
use App\Livewire\Homepage\FeedbackForms\Users\ProgramFeedbackUsers;
use App\Livewire\Homepage\FeedbackForms\Users\UserFeedbackDetails;
use App\Livewire\Homepage\Index as HomepageIndex;
use App\Livewire\Homepage\Liabilities\Liabilities;
use App\Livewire\Homepage\Liabilities\LiabilityManage;
use App\Livewire\Homepage\Liabilities\Users\LiabilityUserDetails;
use App\Livewire\Homepage\Liabilities\Users\LiabilityUserList;
use App\Livewire\Homepage\Methodologies\Methodologies;
use App\Livewire\Homepage\Methodologies\MethodologyManage;
use App\Livewire\Homepage\Methodologies\MethodologyQuestions\MethodologyQuestions;
use App\Livewire\Homepage\Methodologies\Users\MethodologyUserDetails;
use App\Livewire\Homepage\Methodologies\Users\MethodologyUserList;
use App\Livewire\Homepage\Modules\Modules;
use App\Livewire\Homepage\Pillars\Pillars;
use App\Livewire\Homepage\Programs\ProgramManage;
use App\Livewire\Homepage\Programs\Programs;
use App\Livewire\Homepage\Programs\Users\ProgramUserDetails;
use App\Livewire\Homepage\Programs\Users\ProgramUserList;
use App\Livewire\Homepage\Questions\Questions;
use App\Livewire\Homepage\Tags\Tags;
use App\Livewire\Homepage\Users\Admins\Admins;
use App\Livewire\Homepage\Users\Customers\Customers;
use App\Livewire\Homepage\Users\Experts\Experts;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('homepage.index');
});

Route::prefix('app')->get('/login', Login::class)->name('login');
Route::prefix('app')->get('/reset-password/enter-email', ResetPasswordEnterEmail::class)->name('password.enter-email');
Route::prefix('app')->get('/reset-password/2fa', ResetPassword2fa::class)->name('password.2fa');
Route::prefix('app')->get('/reset-password/change-password', ResetPasswordChangePassword::class)->name('password.reset');
Route::prefix('app')->get('/reset-password/password-changed', ResetPasswordChanged::class)->name('password.changed');

Route::prefix('app')->get('/home', HomepageIndex::class)->name('homepage.index');

// Users Management
Route::prefix('app')->get('/admins', Admins::class)->name('admins');
Route::prefix('app')->get('/experts', Experts::class)->name('experts');
Route::prefix('app')->get('/customers', Customers::class)->name('customers');

// Methodology Management
Route::prefix('app')->get('/methodologies', Methodologies::class)->name('methodologies');
Route::prefix('app')->get('/methodology/{methodologyId}', MethodologyManage::class)->name('methodology.manage');
Route::prefix('app')->get('/methodology/{methodologyId}/questions', MethodologyQuestions::class)->name('methodology.questions');

// User Answers Per Methodology
Route::prefix('app')->get('/methodology/{methodology}/users', MethodologyUserList::class)->name('methodology.users');
Route::prefix('app')->get('/methodology/{methodology}/user/{user}', MethodologyUserDetails::class)->name('methodology.user.answers');

// Banks
Route::prefix('app')->get('/pillars', Pillars::class)->name('pillars');
Route::prefix('app')->get('/modules', Modules::class)->name('modules');
Route::prefix('app')->get('/questions', Questions::class)->name('questions');
Route::prefix('app')->get('/tags', Tags::class)->name('tags');

// Programs Management
Route::prefix('app')->get('/programs', Programs::class)->name('programs');
Route::prefix('app')->get('/program/{programId}', ProgramManage::class)->name('program.manage');

// User Answers Per Program
Route::prefix('app')->get('/program/{program}/users', ProgramUserList::class)->name('program.users');
Route::prefix('app')->get('/program/{program}/user/{user}', ProgramUserDetails::class)->name('program.user.answers');

// Liabilities Management
Route::prefix('app')->get('/liabilities', Liabilities::class)->name('liabilities');
Route::prefix('app')->get('/liability/{liabilityId}', LiabilityManage::class)->name('liability.manage');

// User Answers Per Liability
Route::prefix('app')->get('/liability/{liability}/users', LiabilityUserList::class)->name('liability.users');
Route::prefix('app')->get('/liability/{liability}/user/{user}', LiabilityUserDetails::class)->name('liability.user.details');

// Enrichments Management
Route::prefix('app')->get('/enrichments', \App\Livewire\Homepage\Enrichments\Enrichments::class)->name('enrichments');
Route::prefix('app')->get('/enrichment/{enrichmentId}', \App\Livewire\Homepage\Enrichments\EnrichmentManage::class)->name('enrichment.manage');
Route::prefix('app')->get('/categories', \App\Livewire\Homepage\Categories\Categories::class)->name('categories');
Route::prefix('app')->get('/interests', \App\Livewire\Homepage\Interests\Interests::class)->name('interests');

// Feedback Management
Route::prefix('app')->get('/feedback', Feedback::class)->name('feedback');
Route::prefix('app')->get('/feedback-forms', FeedbackFormManagement::class)->name('feedback-forms');
Route::prefix('app')->get('/program/{program}/feedback', ProgramFeedbackUsers::class)->name('program.feedback.users');
Route::prefix('app')->get('/program/{program}/feedback/user/{user}', UserFeedbackDetails::class)->name('program.feedback.user.details');

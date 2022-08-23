<?php


use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

User::get();

User::first();
// User::firstOrFail();

User::find(1);
// User::findOrFail(1);
// User::findMany(1, 2, 3);

User::skip(10)->get();

User::offset(10)->get();

User::take(10)->get();

User::limit(10)->get();

// User::select('name')->get();
// User::query()->addSelect('name')->get();

User::where('name', 'Marek')->get();
User::where('name', '==', 'Marek')->get();
// // User::orWhere('name', 'Marek')->get();
User::where([['name', 'Marek']])->get();

User::whereNot('name', 'Marek')->get();
// // User::orWhereNot('name', 'Marek')->get();

User::whereKey(1)->get();
User::whereKeyNot(1)->get();

User::whereFirst('name', 'Marek');

User::whereIn('name', ['Marek'])->get();
// User::orWhereIn('name', ['Marek'])->get();
User::whereNotIn('name', ['Marek'])->get();
// User::orWhereNotIn('name', ['Marek'])->get();

User::whereNull('name')->get();
// User::orWhereNull('name')->get();
User::whereNotNull('name')->get();
// User::orWhereNotNull('id')->get();

// User::whereBetween('id', [1, 5])->get();
// User::orWhereBetween('id', [1, 5])->get();
// User::whereNotBetween('id', [1, 5])->get();
// User::orWhereNotBetween('id', [1, 5])->get();

// User::whereDate('created_at')->get();
// User::orWhereDate('created_at')->get();

// User::whereTime('created_at')->get();
// User::orWhereTime('created_at')->get();

// User::whereDay('created_at')->get();
// User::orWhereDay('created_at')->get();

// User::whereMonth('created_at')->get();
// User::orWhereMonth('created_at')->get();

// User::whereYear('created_at')->get();
// User::orWhereYear('created_at')->get();

// User::whereFullText('name')->get();
// User::orWhereFullText('name')->get();

// User::groupBy('name')->get();

User::orderBy('created_at')->get();
User::orderByDesc('created_at')->get();
User::latest('created_at')->get();
User::oldest('created_at')->get();

User::forPage(1)->get();

User::where('name', 'Marek')->exists();
User::where('name', 'Marek')->doesntExist();

$user = User::find(1)->fresh();
User::find(1)->refresh();

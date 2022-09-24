<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$query = User::query()->getQuery();

// Initiate request with query
$request = \Lorinczdev\Modely\Http\ApiRequest::use($query);

// To send http request we need to provide action and data
$request->send('update', ['name' => 'Keram']);

// Idea to extend requests
// To have an option to register custom request for each action
// we could set up a directory structure like this:
// '/users/update' => '/users' is the model name in plural form
//                    '/update' is the action name
//                  optionaly the method could be specified as well '/users/update/post'
// or another option would be:
// use filename like this 'UsersUpdatePostRequest'
// where the part 'Users' is the plural name of the model
// 'Update' is the action name
// 'Post' is the method name
// and Request indicates that the files is the extenstion of the Request

// Other ideas:

// UpdateUsers
// DestroyUser

// UsersUpdate
// UsersDestroy
// UsersPromote

// UserPromote
// UserDestroy
// UserGet

// PromoteUser
// DestroyUser
// UpdateUser
// IndexUser
// ShowUser
// StoreUser

// Preferred way to register custom requests:

// /Users
// /Users/IndexRequest
// /Users/ShowRequest
// /Users/StoreRequest
// /Users/UpdateRequest
// /Users/DestroyRequest

// And with method
// /Users/IndexPostRequest
// /Users/PromotePostRequest

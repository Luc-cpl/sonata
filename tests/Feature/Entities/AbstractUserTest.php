<?php

use Sonata\Entities\Abstracts\AbstractUser;

class User extends AbstractUser
{
	public function forceOldHashPassword(string $password): void
	{
		$this->password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 9]);
	}
}

it('should be able to verify a password', function () {
	$user = new User;
	$user->set(password: 'password');

	expect($user->verifyPassword('password'))->toBeTrue();
	expect($user->verifyPassword('wrong password'))->toBeFalse();
});

it('should be able to set a password', function () {
	$user = new User;
	$user->set(password: 'password');

	expect($user->password)->not->toBe('password');
	expect(password_verify('password', $user->password))->toBeTrue();
});

it('should rehash the password when the password is updated', function () {
	$user = new User;
	$user->forceOldHashPassword('password');

	$initial = $user->password;
	$user->verifyPassword('password'); // Trigger rehash
	
	expect($user->password)->not->toBe($initial);
});

it('should be able to set an email', function () {
	$user = new User;
	$user->set(email: 'valid@email.com');
	expect($user->email)->toBe('valid@email.com');
});

it('should throw an exception when setting an invalid email', function (string $email) {
	$user = new User;
	$user->set(email: $email);
})->with([
	'invalid email',
	'invalid.email.com',
	'invalid@.com',
	'invalid@email',
])->expectException(InvalidArgumentException::class);
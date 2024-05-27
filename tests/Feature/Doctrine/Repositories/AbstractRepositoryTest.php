<?php

use Orkestra\Providers\HooksProvider;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Testing\Doctrine;
use Tests\Entities\DoctrineSubject as Subject;

beforeEach(function () {
	doctrineTest();
});

it('should be able to create a subject', function () {
	$repository = app()->get(RepositoryInterface::class);

	$subject = factory()->make(Subject::class);
	$repository->persist($subject);
	Doctrine::flush();

	$foundSubject = Doctrine::find(Subject::class, $subject->id);

	expect($subject->id)->toBeInt();
	expect($foundSubject->id)->toBe($subject->id);
});

it('should be able to update a subject', function () {
	$subject = Doctrine::factory(Subject::class)[0];

	$subject->set(value: 'new value');

	/**
	 * The managed entity should be updated
	 * when the entity manager is flushed.
	 */
	Doctrine::flush();
	$foundSubject = Doctrine::find(Subject::class, $subject->id);

	expect($foundSubject->value)->toBe('new value');
});

describe('listeners', function () {
	beforeEach(function () {
		app()->provider(HooksProvider::class);
	});

	it('persists data on "http.router.response.before" hook', function () {
		$repository = app()->get(RepositoryInterface::class);
		$subject = factory()->make(subject::class);

		$repository->persist($subject);

		expect(isset($subject->id))->toBeFalse();

		app()->hookCall('http.router.response.before');

		expect(isset($subject->id))->toBeTrue();
	});
});

it('should be able to delete a subject', function () {
	$subject = Doctrine::factory(subject::class)[0];
	$id = $subject->id;

	$repository = app()->get(RepositoryInterface::class);
	$repository->remove($subject);

	Doctrine::flush();

	expect($id)->toBeInt();
	expect(isset($subject->id))->toBeFalse();
	expect(Doctrine::find(subject::class, $id))->toBeNull();
});

it('should be able to find a subject by id', function () {
	$subjects = Doctrine::factory(subject::class, 10);

	$repository = app()->get(RepositoryInterface::class);
	$foundSubjects = $repository->whereId($subjects[0]->id)->first();

	expect($foundSubjects->id)->toBe($subjects[0]->id);

	$subjectsIds = array_map(fn ($subject) => $subject->id, $subjects);
	$subjectsIds = array_slice($subjectsIds, 0, 5);

	$foundSubjects = $repository->whereId($subjectsIds)->getIterator();
	$foundSubjects = iterator_to_array($foundSubjects);
	$foundSubjects = array_map(fn ($subject) => $subject->id, $foundSubjects);

	sort($subjectsIds);
	sort($foundSubjects);

	expect(count($foundSubjects))->toBe(5);
	expect($foundSubjects)->toBe($subjectsIds);
});

it('should be able to paginate subjects', function () {
	$subjects = Doctrine::factory(subject::class, 10);

	$repository = app()->get(RepositoryInterface::class);
	$foundSubjects = $repository->slice(0, 5)->getIterator();
	$foundSubjects = iterator_to_array($foundSubjects);
	$foundSubjects = array_map(fn ($subject) => $subject->value, $foundSubjects);

	expect(count($foundSubjects))->toBe(5);

	$subjectsEmails = array_map(fn ($subject) => $subject->value, $subjects);
	$subjectsEmails = array_slice($subjectsEmails, 0, 5);

	sort($subjectsEmails);
	sort($foundSubjects);

	expect($foundSubjects)->toBe($subjectsEmails);
});

it('should be able to count subjects', function () {
	Doctrine::factory(subject::class, 10);
	$repository = app()->get(RepositoryInterface::class);

	$count = $repository->count();
	expect($count)->toBe(10);
});

it('should be able slice and count subjects', function () {
	Doctrine::factory(subject::class, 10);
	$repository = app()->get(RepositoryInterface::class);

	$count = count($repository->slice(0, 5)->getIterator());
	expect($count)->toBe(5);

	$count = count($repository->slice(8, 5)->getIterator());
	expect($count)->toBe(2);

	$count = $repository->slice(0, 5)->count();
	expect($count)->toBe(5);

	$count = $repository->slice(8, 5)->count();
	expect($count)->toBe(2);
});
<?php

namespace AssignmentFiveTests\RouterTests;

use AssignmentFiveTests\RouterTests\RouterTest;

final class CommentRouterTest extends RouterTest
{
	public function testCommentWasCreatedSuccessfully(): void
	{
		$userData = $this->generateUserData();
		$user = $this->generateUser(...array_values($userData));
		$commentData = $this->generateCommentData($user);

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$response = $this->getResponse(
			'POST',
			'comment',
			$commentData
		);

		$this->assertArrayHasKey('message', $response);
		$this->assertArrayHasKey('payload', $response);
		$this->assertArrayHasKey('id', $response['payload']);
		$this->assertArrayHasKey('user', $response['payload']);
		$this->assertArrayHasKey('post', $response['payload']);
		$this->assertArrayHasKey('reply', $response['payload']);
		$this->assertArrayHasKey('content', $response['payload']);
		$this->assertArrayHasKey('replies', $response['payload']);
		$this->assertEquals(1, $response['payload']['id']);
		$this->assertEquals($commentData['userId'], $response['payload']['user']['id']);
		$this->assertEquals($commentData['postId'], $response['payload']['post']['id']);
		$this->assertEmpty($response['payload']['reply']);
		$this->assertEquals($commentData['content'], $response['payload']['content']);
	}

	/**
	 * @dataProvider createCommentProvider
	 */
	public function testCommentWasNotCreated(array $commentData, string $message): void
	{
		$userData = $this->generateUserData();
		$user = $this->generateUser(...array_values($userData));
		self::generatePost(null, $user);

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$response = $this->getResponse(
			'POST',
			'comment',
			$commentData
		);

		$this->assertEmpty($response['payload']);
		$this->assertEquals($message, $response['message']);
	}

	public function createCommentProvider()
	{
		yield 'string user ID' => [
			[
				'userId' => 'abc',
				'postId' => 1,
				'content' => 'You call that a top 3 pick?!'
			],
			'Cannot create Comment: User ID must be an integer.'
		];

		yield 'string post ID' => [
			[
				'userId' => 1,
				'postId' => 'abc',
				'content' => 'You call that a top 3 pick?!'
			],
			'Cannot create Comment: Post ID must be an integer.'
		];

		yield 'invalid post ID' => [
			[
				'userId' => 1,
				'postId' => 999,
				'content' => 'You call that a top 3 pick?!'
			],
			'Cannot create Comment: Post does not exist with ID 999.'
		];

		yield 'blank content' => [
			[
				'userId' => 1,
				'postId' => 1,
				'content' => ''
			],
			'Cannot create Comment: Missing content.'
		];
	}

	public function testCommentWasNotCreatedWhenNotLoggedIn(): void
	{
		$response = $this->getResponse(
			'POST',
			'comment',
			$this->generateCommentData()
		);

		$this->assertEquals('Cannot create Comment: You must be logged in.', $response['message']);
		$this->assertEmpty($response['payload']);
	}

	public function testCommentWasNotCreatedByAnotherUser(): void
	{
		$userA = $this->generateUser();
		$userDataB = $this->generateUserData();
		$this->generateUser(...array_values($userDataB));
		$commentData = $this->generateCommentData($userA);

		$this->getResponse(
			'POST',
			'auth/login',
			$userDataB
		);

		$createdComment = $this->getResponse(
			'POST',
			'comment',
			$commentData
		);

		$this->assertEquals('Cannot create Comment: You cannot create a comment for someone else!', $createdComment['message']);
		$this->assertEmpty($createdComment['payload']);
	}

	public function testCommentWasFoundById(): void
	{
		$comment = $this->generateComment();

		$retrievedComment = $this->getResponse(
			'GET',
			'comment/' . $comment->getId()
		)['payload'];

		$this->assertArrayHasKey('id', $retrievedComment);
		$this->assertArrayHasKey('user', $retrievedComment);
		$this->assertArrayHasKey('post', $retrievedComment);
		$this->assertArrayHasKey('content', $retrievedComment);
		$this->assertEquals($comment->getId(), $retrievedComment['id']);
		$this->assertEquals($comment->getUser()->getId(), $retrievedComment['user']['id']);
		$this->assertEquals($comment->getPost()->getId(), $retrievedComment['post']['id']);
		$this->assertEquals($comment->getContent(), $retrievedComment['content']);
	}

	public function testCommentWasNotFoundByWrongId(): void
	{
		$retrievedComment = $this->getResponse(
			'GET',
			'comment/1',
		);

		$this->assertEquals('Cannot find Comment: Comment does not exist with ID 1.', $retrievedComment['message']);
		$this->assertEmpty($retrievedComment['payload']);
	}

	/**
	 * @dataProvider updatedCommentProvider
	 */
	public function testCommentWasUpdated(array $oldCommentData, array $newCommentData, array $editedFields): void
	{
		$this->generateComment();
		$userData = $this->generateUserData();
		$user = $this->generateUser(...array_values($userData));
		$oldCommentData['userId'] = $user->getId();

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$oldComment = $this->getResponse(
			'POST',
			'comment',
			$oldCommentData
		)['payload'];

		$editedComment = $this->getResponse(
			'PUT',
			'comment/' . $oldComment['id'],
			$newCommentData
		)['payload'];

		/**
		 * Check every Comment field against all the fields that were supposed to be edited.
		 * If the Comment field is a field that's supposed to be edited, check if they're not equal.
		 * If the Comment field is not supposed to be edited, check if they're equal.
		 */
		foreach ($oldComment as $oldCommentKey => $oldCommentValue) {
			foreach ($editedFields as $editedField) {
				if ($oldCommentKey === $editedField) {
					$this->assertNotEquals($oldCommentValue, $editedComment[$editedField]);
					$this->assertEquals($editedComment[$editedField], $newCommentData[$editedField]);
				}
			}
		}
	}

	public function updatedCommentProvider()
	{
		yield 'valid content' => [
			['postId' => 1, 'userId' => 1, 'content' => 'pikachu@pokemon.com', 'replyId' => null],
			['content' => 'Bulbasaur'],
			['content'],
		];
	}

	/**
	 * @dataProvider updateCommentProvider
	 */
	public function testCommentWasNotUpdated(int $commentId, array $newCommentData, string $message): void
	{
		$userData = $this->generateUserData();
		$user = $this->generateUser(...array_values($userData));
		$this->generateComment($user);

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$editedComment = $this->getResponse(
			'PUT',
			'comment/' . $commentId,
			$newCommentData
		);

		$this->assertEquals($message, $editedComment['message']);
		$this->assertEmpty($editedComment['payload']);
	}

	public function updateCommentProvider()
	{
		yield 'blank content' => [
			1,
			['content' => ''],
			'Cannot edit Comment: Missing content.'
		];
	}

	public function testCommentWasNotUpdatedWhenNotLoggedIn(): void
	{
		$comment = $this->generateComment();

		$editedComment = $this->getResponse(
			'PUT',
			'comment/' . $comment->getId(),
			$this->generateCommentData()
		);

		$this->assertEquals('Cannot edit Comment: You must be logged in.', $editedComment['message']);
		$this->assertEmpty($editedComment['payload']);
	}

	public function testCommentWasNotUpdatedByAnotherUser(): void
	{
		$userA = $this->generateUser();
		$comment = $this->generateComment($userA);
		$userDataB = $this->generateUserData();
		$this->generateUser(...array_values($userDataB));

		$this->getResponse(
			'POST',
			'auth/login',
			$userDataB
		);

		$editedComment = $this->getResponse(
			'PUT',
			'comment/' . $comment->getId(),
			$this->generateCommentData()
		);

		$this->assertEquals('Cannot edit Comment: You cannot edit a comment that you did not create!', $editedComment['message']);
		$this->assertEmpty($editedComment['payload']);
	}

	public function testCommentWasDeletedSuccessfully(): void
	{
		$userData = $this->generateUserData();
		$user = $this->generateUser(...array_values($userData));
		$comment = $this->generateComment($user);

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$this->assertEmpty($comment->getDeletedAt());

		$deletedComment = $this->getResponse(
			'DELETE',
			'comment/' . $comment->getId()
		)['payload'];

		$this->assertEquals($comment->getId(), $deletedComment['id']);
		$this->assertEquals($comment->getContent(), $deletedComment['content']);

		$retrievedComment = $this->getResponse(
			'GET',
			'comment/' . $comment->getId(),
		)['payload'];

		$this->assertNotEmpty($retrievedComment['deletedAt']);
	}

	public function testCommentWasNotDeletedWithInvalidId(): void
	{
		$userData = $this->generateUserData();
		$this->generateUser(...array_values($userData));

		$this->getResponse(
			'POST',
			'auth/login',
			$userData
		);

		$deletedComment = $this->getResponse(
			'DELETE',
			'comment/999'
		);

		$this->assertEquals('Cannot delete Comment: Comment does not exist with ID 999.', $deletedComment['message']);
		$this->assertEmpty($deletedComment['payload']);
	}

	public function testCommentWasNotDeletedWhenNotLoggedIn(): void
	{
		$user = $this->generateUser();
		$comment = $this->generateComment($user);

		$deletedComment = $this->getResponse(
			'DELETE',
			'comment/' . $comment->getId(),
			$this->generateCommentData()
		);

		$this->assertEquals('Cannot delete Comment: You must be logged in.', $deletedComment['message']);
		$this->assertEmpty($deletedComment['payload']);
	}

	public function testCommentWasNotDeletedByAnotherUser(): void
	{
		$userA = $this->generateUser();
		$comment = $this->generateComment($userA);
		$userDataB = $this->generateUserData();
		$this->generateUser(...array_values($userDataB));

		$this->getResponse(
			'POST',
			'auth/login',
			$userDataB
		);

		$deletedComment = $this->getResponse(
			'DELETE',
			'comment/' . $comment->getId()
		);

		$this->assertEquals('Cannot delete Comment: You cannot delete a comment that you did not create!', $deletedComment['message']);
		$this->assertEmpty($deletedComment['payload']);
	}
}

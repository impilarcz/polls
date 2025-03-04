<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author René Gieling <github@dartcafe.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Polls\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCA\Polls\Service\CommentService;

class CommentController extends Controller {

	/** @var CommentService */
	private $commentService;

	use ResponseHandle;

	/**
	 * CommentController constructor
	 * @param string $appName
	 * @param IRequest $request
	 * @param CommentService $commentService
	 */

	public function __construct(
		string $appName,
		IRequest $request,
		CommentService $commentService
	) {
		parent::__construct($appName, $request);
		$this->commentService = $commentService;
	}

	/**
	 * Write a new comment to the db and returns the new comment as array
	 * @NoAdminRequired
	 * @PublicPage
	 * @param int $pollId
	 * @param string $message
	 * @param string $token
	 * @return DataResponse
	 */
	public function add($pollId, $message, $token) {
		return $this->response(function () use ($pollId, $message, $token) {
			return ['comment'=> $this->commentService->add($pollId, $message, $token)];
		});
	}

	/**
	 * Delete Comment
	 * @NoAdminRequired
	 * @PublicPage
	 * @param int $commentId
	 * @param string $token
	 * @return DataResponse
	 */
	public function delete($commentId, $token) {
		return $this->responseDeleteTolerant(function () use ($commentId, $token) {
			return ['comment'=> $this->commentService->delete($commentId, $token)];
		});
	}
}

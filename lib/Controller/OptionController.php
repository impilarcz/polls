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

use DateTime;
use DateInterval;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCA\Polls\Service\OptionService;
use OCA\Polls\Service\CalendarService;

class OptionController extends Controller {

	/** @var OptionService */
	private $optionService;

	/** @var CalendarService */
	private $calendarService;

	use ResponseHandle;

	/**
	 * OptionController constructor.
	 * @param string $appName
	 * @param IRequest $request
	 * @param OptionService $optionService
	 */

	public function __construct(
		string $appName,
		IRequest $request,
		OptionService $optionService,
		CalendarService $calendarService
	) {
		parent::__construct($appName, $request);
		$this->optionService = $optionService;
		$this->calendarService = $calendarService;
	}

	/**
	 * Get all options of given poll
	 * @NoAdminRequired
	 * @param int $pollId
	 * @return DataResponse
	 */
	public function list($pollId) {
		return $this->response(function () use ($pollId) {
			return ['options' => $this->optionService->list($pollId)];
		});
	}

	/**
	 * Add a new option
	 * @NoAdminRequired
	 * @param array $option
	 * @return DataResponse
	 */
	public function add($pollId, $timestamp = 0, $pollOptionText = '') {
		return $this->responseCreate(function () use ($pollId, $timestamp, $pollOptionText) {
			return ['option' => $this->optionService->add($pollId, $timestamp, $pollOptionText)];
		});
	}

	/**
	 * Update option
	 * @NoAdminRequired
	 * @param array $option
	 * @return DataResponse
	 */
	public function update($optionId, $timestamp, $pollOptionText) {
		return $this->response(function () use ($optionId, $timestamp, $pollOptionText) {
			return ['option' => $this->optionService->update($optionId, $timestamp, $pollOptionText)];
		});
	}

	/**
	 * Delete option
	 * @NoAdminRequired
	 * @param Option $option
	 * @return DataResponse
	 */
	public function delete($optionId) {
		return $this->responseDeleteTolerant(function () use ($optionId) {
			return ['option' => $this->optionService->delete($optionId)];
		});
	}
	/**
	 * Switch option confirmation
	 * @NoAdminRequired
	 * @param int $optionId
	 * @return DataResponse
	 */
	public function confirm($optionId) {
		return $this->response(function () use ($optionId) {
			return ['option' => $this->optionService->confirm($optionId)];
		});
	}

	/**
	 * Reorder options
	 * @NoAdminRequired
	 * @param int $pollId
	 * @param Array $options
	 * @return DataResponse
	 */
	public function reorder($pollId, $options) {
		return $this->response(function () use ($pollId, $options) {
			return ['options' => $this->optionService->reorder($pollId, $options)];
		});
	}

	/**
	 * Reorder options
	 * @NoAdminRequired
	 * @param int $optionId
	 * @param int $step
	 * @param string $unit
	 * @param int $amount
	 * @return DataResponse
	 */
	public function sequence($optionId, $step, $unit, $amount) {
		return $this->response(function () use ($optionId, $step, $unit, $amount) {
			return ['options' => $this->optionService->sequence($optionId, $step, $unit, $amount)];
		});
	}

	/**
	 * findCalendarEvents
	 * @NoAdminRequired
	 * @param integer $from
	 * @param integer $to
	 * @return DataResponse
	 */
	public function findCalendarEvents($optionId) {
		return $this->response(function () use ($optionId) {
			// try {
			$searchFrom = new DateTime();
			$searchFrom = $searchFrom->setTimestamp($this->optionService->get($optionId)->getTimestamp())->sub(new DateInterval('PT1H'));
			$searchTo = clone $searchFrom;
			$searchTo = $searchTo->add(new DateInterval('PT3H'));
			$events = $this->calendarService->getEvents($searchFrom, $searchTo);

			return ['events' => $events];
			// } catch (\Exception $e) {
			// 	return ['events' => []];
			// }
		});
	}
}

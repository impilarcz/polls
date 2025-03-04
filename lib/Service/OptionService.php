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

namespace OCA\Polls\Service;

use DateTime;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\Polls\Exceptions\NotAuthorizedException;
use OCA\Polls\Exceptions\BadRequestException;
use OCA\Polls\Exceptions\DuplicateEntryException;
use OCA\Polls\Exceptions\NotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\PollMapper;
use OCA\Polls\Db\Poll;
use OCA\Polls\Model\Acl;

class OptionService {

	/** @var OptionMapper */
	private $optionMapper;

	/** @var Option */
	private $option;

	/** @var PollMapper */
	private $pollMapper;

	/** @var Acl */
	private $acl;

	/**
	 * OptionService constructor.
	 * @param OptionMapper $optionMapper
	 * @param Option $option
	 * @param PollMapper $pollMapper
	 * @param Acl $acl
	 */

	public function __construct(
		OptionMapper $optionMapper,
		Option $option,
		PollMapper $pollMapper,
		Acl $acl
	) {
		$this->optionMapper = $optionMapper;
		$this->option = $option;
		$this->pollMapper = $pollMapper;
		$this->acl = $acl;
	}

	/**
	 * Get all options of given poll
	 * @NoAdminRequired
	 * @param int $pollId
	 * @param string $token
	 * @return array Array of Option objects
	 * @throws NotAuthorizedException
	 */
	public function list($pollId = 0, $token = '') {
		$acl = $this->acl->set($pollId, $token);

		if (!$acl->getAllowView()) {
			throw new NotAuthorizedException;
		}

		try {
			return $this->optionMapper->findByPoll($acl->getPollId());
		} catch (DoesNotExistException $e) {
			return [];
		}
	}

	/**
	 * Get option
	 * @NoAdminRequired
	 * @param int $optionId
	 * @return Option
	 * @throws NotAuthorizedException
	 */
	public function get($optionId) {
		if (!$this->acl->set($this->optionMapper->find($optionId)->getPollId())->getAllowView()) {
			throw new NotAuthorizedException;
		}

		return $this->optionMapper->find($optionId);
	}


	/**
	 * Add a new option
	 * @NoAdminRequired
	 * @param int $pollId
	 * @param int $timestamp
	 * @param string $pollOptionText
	 * @return Option
	 * @throws NotAuthorizedException
	 */
	public function add($pollId, $timestamp = 0, $pollOptionText = '') {
		if (!$this->acl->set($pollId)->getAllowEdit()) {
			throw new NotAuthorizedException;
		}

		$this->option = new Option();
		$this->option->setPollId($pollId);
		$this->option->setOrder($this->getHighestOrder($this->option->getPollId()) + 1);
		$this->setOption($timestamp, $pollOptionText);

		try {
			return $this->optionMapper->insert($this->option);
		} catch (UniqueConstraintViolationException $e) {
			throw new DuplicateEntryException('This option already exists');
		}
	}

	/**
	 * Update option
	 * @NoAdminRequired
	 * @param int $optionId
	 * @param int $timestamp
	 * @param string $pollOptionText
	 * @param int $order
	 * @return Option
	 * @throws NotAuthorizedException
	 */
	public function update($optionId, $timestamp = 0, $pollOptionText = '') {
		$this->option = $this->optionMapper->find($optionId);

		if (!$this->acl->set($this->option->getPollId())->getAllowEdit()) {
			throw new NotAuthorizedException;
		}

		$this->setOption($timestamp, $pollOptionText);

		return $this->optionMapper->update($this->option);
	}

	/**
	 * Delete option
	 * @NoAdminRequired
	 * @param int $optionId
	 * @return Option deleted Option
	 * @throws NotAuthorizedException
	 */
	public function delete($optionId) {
		$this->option = $this->optionMapper->find($optionId);

		if (!$this->acl->set($this->option->getPollId())->getAllowEdit()) {
			throw new NotAuthorizedException;
		}

		$this->optionMapper->delete($this->option);

		return $this->option;
	}

	/**
	 * Switch optoin confirmation
	 * @NoAdminRequired
	 * @param int $optionId
	 * @return Option confirmed Option
	 * @throws NotAuthorizedException
	 */
	public function confirm($optionId) {
		$this->option = $this->optionMapper->find($optionId);

		if (!$this->acl->set($this->option->getPollId())->getAllowEdit()) {
			throw new NotAuthorizedException;
		}

		if ($this->option->getConfirmed()) {
			$this->option->setConfirmed(0);
		} else {
			$this->option->setConfirmed(time());
		}

		return $this->optionMapper->update($this->option);
	}

	/**
	 * Make a sequence of date poll options
	 * @NoAdminRequired
	 * @param int $optionId
	 * @param int $step
	 * @param string $unit
	 * @param int $amount
	 * @return array Array of Option objects
	 * @throws NotAuthorizedException
	 */
	public function sequence($optionId, $step, $unit, $amount) {
		$baseDate = new DateTime;
		$origin = $this->optionMapper->find($optionId);

		if (!$this->acl->set($origin->getPollId())->getAllowEdit()) {
			throw new NotAuthorizedException;
		}

		if ($step === 0) {
			return $this->optionMapper->findByPoll($origin->getPollId());
		}

		$baseDate->setTimestamp($origin->getTimestamp());

		for ($i = 0; $i < $amount; $i++) {
			$this->option = new Option();
			$this->option->setPollId($origin->getPollId());
			$this->option->setConfirmed(0);
			$this->option->setTimestamp($baseDate->modify($step . ' ' . $unit)->getTimestamp());
			$this->option->setPollOptionText($baseDate->format('c'));
			$this->option->setOrder($baseDate->getTimestamp());
			try {
				$this->optionMapper->insert($this->option);
			} catch (UniqueConstraintViolationException $e) {
				\OC::$server->getLogger()->warning('skip adding ' . $baseDate->format('c') . 'for pollId' . $origin->getPollId() . '. Option alredy exists.');
			}
		}
		return $this->optionMapper->findByPoll($origin->getPollId());
	}

	/**
	 * Copy options from $fromPoll to $toPoll
	 * @NoAdminRequired
	 * @param int $fromPollId
	 * @param int $toPollId
	 * @return array Array of Option objects
	 * @throws NotAuthorizedException
	 */
	public function clone($fromPollId, $toPollId) {
		try {
			if (!$this->acl->set($fromPollId)->getAllowView()) {
				throw new NotAuthorizedException;
			}
		} catch (DoesNotExistException $e) {
			throw new NotFoundException('Poll ' . $fromPollId . ' does not exist');
		}

		foreach ($this->optionMapper->findByPoll($fromPollId) as $origin) {
			$option = new Option();
			$option->setPollId($toPollId);
			$option->setConfirmed(0);
			$option->setPollOptionText($origin->getPollOptionText());
			$option->setTimestamp($origin->getTimestamp());
			$option->setOrder($origin->getOrder());
			$this->optionMapper->insert($option);
		}

		return $this->optionMapper->findByPoll($toPollId);
	}

	/**
	 * Reorder options with the order specified by $options
	 * @NoAdminRequired
	 * @param int $pollId
	 * @param array $options - Array of options
	 * @return array Array of Option objects
	 * @throws NotAuthorizedException
	 * @throws BadRequestException
	 * @throws NotFoundException
	 */
	public function reorder($pollId, $options) {
		try {
			if (!$this->acl->set($pollId)->getAllowEdit()) {
				throw new NotAuthorizedException;
			}

			if ($this->pollMapper->find($pollId)->getType() === Poll::TYPE_DATE) {
				throw new BadRequestException("Not allowed in date polls");
			}
		} catch (DoesNotExistException $e) {
			throw new NotAuthorizedException;
		}

		$i = 0;
		foreach ($options as $option) {
			$this->option = $this->optionMapper->find($option['id']);
			if ($pollId === intval($this->option->getPollId())) {
				$this->option->setOrder(++$i);
				$this->optionMapper->update($this->option);
			}
		}

		return $this->optionMapper->findByPoll($pollId);
	}

	/**
	 * Change order for $optionId and reorder the options
	 * @NoAdminRequired
	 * @param int $optionId
	 * @param int $newOrder
	 * @return array Array of Option objects
	 * @throws NotAuthorizedException
	 * @throws BadRequestException
	 * @throws NotFoundException
	 */
	public function setOrder($optionId, $newOrder) {
		try {
			$this->option = $this->optionMapper->find($optionId);
			$pollId = $this->option->getPollId();

			if ($this->pollMapper->find($pollId)->getType() === Poll::TYPE_DATE) {
				throw new BadRequestException("Not allowed in date polls");
			}

			if (!$this->acl->set($pollId)->getAllowEdit()) {
				throw new NotAuthorizedException;
			}
		} catch (DoesNotExistException $e) {
			throw new NotAuthorizedException;
		}

		if ($newOrder < 1) {
			$newOrder = 1;
		} elseif ($newOrder > $this->getHighestOrder($pollId)) {
			$newOrder = $this->getHighestOrder($pollId);
		}

		foreach ($this->optionMapper->findByPoll($pollId) as $option) {
			$option->setOrder($this->moveModifier($this->option->getOrder(), $newOrder, $option->getOrder()));
			$this->optionMapper->update($option);
		}

		return $this->optionMapper->findByPoll($this->option->getPollId());
	}

	/**
	 * moveModifier - evaluate new order
	 * depending on the old and the new position of a moved array item
	 * @NoAdminRequired
	 * @param int $moveFrom - old position of the moved item
	 * @param int $moveTo   - target posotion of the moved item
	 * @param int $value    - current position of the current item
	 * @return int          - the modified new new position of the current item
	 */
	private function moveModifier($moveFrom, $moveTo, $currentPosition) {
		$moveModifier = 0;
		if ($moveFrom < $currentPosition && $currentPosition <= $moveTo) {
			// moving forward
			$moveModifier = -1;
		} elseif ($moveTo <= $currentPosition && $currentPosition < $moveFrom) {
			//moving backwards
			$moveModifier = 1;
		} elseif ($moveFrom === $currentPosition) {
			return $moveTo;
		}
		return $currentPosition + $moveModifier;
	}

	/**
	 * Set option entities validated
	 * @NoAdminRequired
	 * @param int $timestamp
	 * @param string $pollOptionText
	 * @param int $order
	 * @throws BadRequestException
	 */
	private function setOption($timestamp = 0, $pollOptionText = '') {
		$poll = $this->pollMapper->find($this->option->getPollId());

		if ($poll->getType() === Poll::TYPE_DATE) {
			$this->option->setTimestamp($timestamp);
			$this->option->setOrder($timestamp);
			$this->option->setPollOptionText(date('c', $timestamp));
		} else {
			$this->option->setPollOptionText($pollOptionText);
		}
	}

	/**
	 * Get the highest order number in $pollId
	 * @NoAdminRequired
	 * @param int $pollId
	 * @return int Highest order number
	 */
	private function getHighestOrder($pollId) {
		$highestOrder = 0;
		foreach ($this->optionMapper->findByPoll($pollId) as $option) {
			if ($option->getOrder() > $highestOrder) {
				$highestOrder = $option->getOrder();
			}
		}
		return $highestOrder;
	}
}

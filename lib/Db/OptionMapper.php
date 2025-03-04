<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
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

namespace OCA\Polls\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class OptionMapper extends QBMapper {

	/**
	 * TextMapper constructor.
	 * @param IDBConnection $db
	 */
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'polls_options', '\OCA\Polls\Db\Option');
	}

	/**
	 * @param int $id
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return Option
	 */

	public function find($id) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
		   );

		return $this->findEntity($qb);
	}

	/**
	 * @param int $pollId
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return array
	 */

	public function findByPoll($pollId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   )
		   ->orderBy('order', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $pollId
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return array
	 */

	public function findByPollAndText($pollId, $pollOptionText) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   )
		   ->andWhere(
			   $qb->expr()->eq('poll_option_text', $qb->createNamedParameter($pollOptionText, IQueryBuilder::PARAM_STR))
		   )
		   ->orderBy('order', 'ASC');

		return $this->findEntity($qb);
	}

	/**
	 * @param int $optionId
	 */
	public function remove($optionId) {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
		   ->where(
			   $qb->expr()->eq('id', $qb->createNamedParameter($optionId, IQueryBuilder::PARAM_INT))
		   );

		$qb->execute();
	}

	/**
	 * @param int $pollId
	 */
	public function deleteByPoll($pollId) {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   );

		$qb->execute();
	}
}

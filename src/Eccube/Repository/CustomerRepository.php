<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Common\Constant;
use Eccube\Entity\Customer;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository extends EntityRepository implements UserProviderInterface
{
    public $app;

    public function setApplication($app)
    {
        $this->app = $app;
    }

    public function newCustomer()
    {
        $Customer = new \Eccube\Entity\Customer();
        $Status = $this->getEntityManager()
            ->getRepository('Eccube\Entity\Master\CustomerStatus')
            ->find(1);

        $Customer
            ->setStatus($Status)
            ->setDelFlg(0);

        return $Customer;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        // 本会員ステータスの会員のみ有効.
        $CustomerStatus = $this
            ->getEntityManager()
            ->getRepository('Eccube\Entity\Master\CustomerStatus')
            ->find(\Eccube\Entity\Master\CustomerStatus::ACTIVE);

        $query = $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->andWhere('c.del_flg = :delFlg')
            ->andWhere('c.Status =:CustomerStatus')
            ->setParameters(array(
                'email' => $username,
                'delFlg' => Constant::DISABLED,
                'CustomerStatus' => $CustomerStatus,
            ))
            ->getQuery();
        $Customer = $query->getOneOrNullResult();
        if (!$Customer) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $Customer;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Eccube\Entity\Customer';
    }

    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->andWhere('c.del_flg = 0');

        if (!empty($searchData['multi']) && $searchData['multi']) {
            if (preg_match('/^\d+$/', $searchData['multi'])) {
                $qb
                    ->andWhere('c.id = :customer_id')
                    ->setParameter('customer_id', $searchData['multi']);
            } else {
                $qb
                    ->andWhere('CONCAT(c.name01, c.name02) LIKE :name OR CONCAT(c.kana01, c.kana02) LIKE :kana OR c.email LIKE :email')
                    ->setParameter('name', '%' . $searchData['multi'] . '%')
                    ->setParameter('kana', '%' . $searchData['multi'] . '%')
                    ->setParameter('email', '%' . $searchData['multi'] . '%');
            }
        }

        // Pref
        if (!empty($searchData['pref']) && $searchData['pref']) {
            $qb
                ->andWhere('c.Pref = :pref')
                ->setParameter('pref', $searchData['pref']->getId());
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            $sexs = array();
            foreach ($searchData['sex'] as $sex) {
                $sexs[] = $sex->getId();
            }

            $qb
                ->andWhere($qb->expr()->in('c.Sex', ':sexs'))
                ->setParameter('sexs', $sexs);
        }

        // birth_month
        if (!empty($searchData['birth_month']) && $searchData['birth_month']) {
            //            TODO: http://docs.symfony.gr.jp/symfony2/cookbook/doctrine/custom_dql_functions.html
//            $qb
//                ->andWhere('extract(month from c.birth) = :birth_month')
//                ->setParameter('birth_month', $searchData['birth_month']);
        }

        // birth
        if (!empty($searchData['birth_start']) && $searchData['birth_start']) {
            $date = $searchData['birth_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth >= :birth_start')
                ->setParameter('birth_start', $date);
        }
        if (!empty($searchData['birth_end']) && $searchData['birth_end']) {
            $date = $searchData['birth_end']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth < :birth_end')
                ->setParameter('birth_end', $date);
        }

        // tel
        if (!empty($searchData['tel']) && $searchData['tel']) {
            $qb
                ->andWhere('CONCAT(c.tel01, c.tel02, c.tel03) LIKE :tel')
                ->setParameter('tel', '%' . $searchData['tel'] . '%');
        }

        // buy_total
        if (!empty($searchData['buy_total_start']) && $searchData['buy_total_start']) {
            $qb
                ->andWhere('c.buy_total >= :buy_total_start')
                ->setParameter('buy_total_start', $searchData['buy_total_start']);
        }
        if (!empty($searchData['buy_total_end']) && $searchData['buy_total_end']) {
            $qb
                ->andWhere('c.buy_total <= :buy_total_end')
                ->setParameter('buy_total_end', $searchData['buy_total_end']);
        }

        // buy_times
        if (!empty($searchData['buy_times_start']) && $searchData['buy_times_start']) {
            $qb
                ->andWhere('c.buy_times >= :buy_times_start')
                ->setParameter('buy_times_start', $searchData['buy_times_start']);
        }
        if (!empty($searchData['buy_times_end']) && $searchData['buy_times_end']) {
            $qb
                ->andWhere('c.buy_times <= :buy_times_end')
                ->setParameter('buy_times_end', $searchData['buy_times_end']);
        }

        // create_date
        if (!empty($searchData['create_date_start']) && $searchData['create_date_start']) {
            $date = $searchData['create_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.create_date >= :create_date_start')
                ->setParameter('create_date_start', $date);
        }
        if (!empty($searchData['create_date_end']) && $searchData['create_date_end']) {
            $date = $searchData['create_date_end']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = $searchData['update_date_end']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // last_buy
        if (!empty($searchData['last_buy_start']) && $searchData['last_buy_start']) {
            $date = $searchData['last_buy_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.last_buy_date >= :last_buy_start')
                ->setParameter('last_buy_start', $date);
        }
        if (!empty($searchData['last_buy_end']) && $searchData['last_buy_end']) {
            $date = $searchData['last_buy_end']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.last_buy_date < :last_buy_end')
                ->setParameter('last_buy_end', $date);
        }

        // status
        if (!empty($searchData['customer_status']) && count($searchData['customer_status']) > 0) {
            $qb
                ->andWhere($qb->expr()->in('c.Status', ':statuses'))
                ->setParameter('statuses', $searchData['customer_status']);
        }

        // buy_product_name、buy_product_code
        if (!empty($searchData['buy_product_code']) && $searchData['buy_product_code']) {
            $qb
                ->leftJoin('c.Orders', 'o')
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name OR od.product_code LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_code'] . '%');
        }

        // Order By
        $qb->addOrderBy('c.update_date', 'DESC');

        return $qb;
    }

    /**
     * ユニークなシークレットキーを返す
     * @param $app
     * @return string
     */
    public function getUniqueSecretKey($app)
    {
        $unique = md5(uniqid(rand(), 1));
        $Customer = $app['eccube.repository.customer']->findBy(array(
            'secret_key' => $unique,
        ));
        if (count($Customer) == 0) {
            return $unique;
        } else {
            return $this->getUniqueSecretKey($app);
        }
    }

    /**
     * ユニークなパスワードリセットキーを返す
     * @param $app
     * @return string
     */
    public function getUniqueResetKey($app)
    {
        $unique = md5(uniqid(rand(), 1));
        $Customer = $app['eccube.repository.customer']->findBy(array(
                        'reset_key' => $unique,
        ));
        if (count($Customer) == 0) {
            return $unique;
        } else {
            return $this->getUniqueResetKey($app);
        }
    }

    /**
     * saltを生成する
     *
     * @param $byte
     * @return string
     */
    public function createSalt($byte)
    {
        $generator = new SecureRandom();

        return bin2hex($generator->nextBytes($byte));
    }

    /**
     * 入力されたパスワードをSaltと暗号化する
     *
     * @param $app
     * @param  Customer $Customer
     * @return mixed
     */
    public function encryptPassword($app, \Eccube\Entity\Customer $Customer)
    {
        $encoder = $app['security.encoder_factory']->getEncoder($Customer);

        return $encoder->encodePassword($Customer->getPassword(), $Customer->getSalt());
    }

    public function getNonActiveCustomerBySecretKey($secret_key)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.del_flg = 0 AND c.secret_key = :secret_key')
            ->leftJoin('c.Status', 's')
            ->andWhere('s.id = :status')
            ->setParameter('secret_key', $secret_key)
            ->setParameter('status', 1);
        $query = $qb->getQuery();

        return $query->getSingleResult();
    }

    public function getActiveCustomerByEmail($email)
    {
        // TODO:Customer.Status -> 先頭小文字では？
        $query = $this->createQueryBuilder('c')
            ->where('c.email = :email AND c.Status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', 2)
            ->getQuery();

        $Customer = $query->getOneOrNullResult();

        return $Customer;
    }

    public function getActiveCustomerByResetKey($reset_key)
    {
        // TODO:Customer.Status -> 先頭小文字では？
        $query = $this->createQueryBuilder('c')
            ->where('c.reset_key = :reset_key AND c.Status = :status AND c.reset_expire >= :reset_expire')
            ->setParameter('reset_key', $reset_key)
            ->setParameter('status', 2)
            ->setParameter('reset_expire', new \DateTime())
            ->getQuery();

        $Customer = $query->getSingleResult();

        return $Customer;
    }

    public function getResetPassword()
    {
        // TODO : これで良いか？(大文字込みならもうちょっと別のやりかたで）
        return substr(base_convert(md5(uniqid()), 16, 36), 0, 8);
    }
}

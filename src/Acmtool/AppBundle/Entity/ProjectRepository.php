<?php

namespace Acmtool\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Acmtool\AppBundle\Entity\Admin;
use Acmtool\AppBundle\Entity\Creds;
use Acmtool\AppBundle\Entity\TeamMember;
use Acmtool\AppBundle\Entity\ConstValues;
use Acmtool\AppBundle\Entity\Customer;
use Acmtool\AppBundle\Entity\CustomerUser;
use Acmtool\AppBundle\Entity\Developer;
use Acmtool\AppBundle\Entity\Designer;
use Acmtool\AppBundle\Entity\Tester;
use Acmtool\AppBundle\Entity\SystemAdmin;
use Acmtool\AppBundle\Entity\KeyAccount;
/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
{
	public function getTotalNumberOfProject()
	{
		$em=$this->getEntityManager();
		$totalcount=$em->createQuery("SELECT COUNT(c) FROM AcmtoolAppBundle:Project")
	            ->getSingleScalarResult();
	    return $totalcount;
	}
	public function getProjectsCountbyKeyAccount($keyaccount,$state)
	{
		$em=$this->getEntityManager();
		$totalcount=0;
		if($state==ProjectStates::ALL)
			$totalcount=$em->createQuery("SELECT COUNT(c) FROM AcmtoolAppBundle:Project c WHERE c.keyaccount = :keyaccount")
				->setParameter("keyaccount",$keyaccount)
	            ->getSingleScalarResult();
	    else
	    	$totalcount=$em->createQuery("SELECT COUNT(c) FROM AcmtoolAppBundle:Project c WHERE c.keyaccount = :keyaccount and c.state= :state")
				->setParameter("keyaccount",$keyaccount)
				->setParameter("state",$state)
	            ->getSingleScalarResult();
        return $totalcount;
	}
	public function getProjectsByKeyAccount($keyaccount,$start,$state)
	{
		$result=null;
		$em=$this->getEntityManager();
		if($state==ProjectStates::ALL)
			$result=$em->createQuery('select c from AcmtoolAppBundle:Project c
								WHERE c.keyaccount = :keyaccount')
						->setParameter("keyaccount",$keyaccount)
                        ->setMaxResults(ConstValues::COUNT)
                        ->setFirstResult($start)
                        ->getResult();
        else
        	$result=$em->createQuery('select c from AcmtoolAppBundle:Project c
								WHERE c.keyaccount = :keyaccount and c.state= :state')
						->setParameter("keyaccount",$keyaccount)
						->setParameter("state",$state)
                        ->setMaxResults(ConstValues::COUNT)
                        ->setFirstResult($start)
                        ->getResult();

        return $result;
	}
	public function getProjectCountByCustomer($customer,$state)
	{
		$em=$this->getEntityManager();
		$totalcount=0;
		if($state==ProjectStates::ALL)
			$totalcount=$em->createQuery("SELECT COUNT(c) FROM AcmtoolAppBundle:Project c WHERE c.owner = :customer")
				->setParameter("customer",$customer)
	            ->getSingleScalarResult();
	    else
	    	$totalcount=$em->createQuery("SELECT COUNT(c) FROM AcmtoolAppBundle:Project c WHERE c.owner = :customer AND c.state= :state")
				->setParameter("customer",$customer)
				->setParameter("state",$state)
	            ->getSingleScalarResult();
        return $totalcount;
	}
	public function getProjectsByCustomer($customer,$start,$state)
	{
		$em=$this->getEntityManager();
		$result=null;
		if($state==ProjectStates::ALL)
			$result=$em->createQuery('select c from AcmtoolAppBundle:Project c
									WHERE c.owner = :customer')
							->setParameter("customer",$customer)
	                        ->setMaxResults(ConstValues::COUNT)
	                        ->setFirstResult($start)
	                        ->getResult();
	    else
	    	$result=$em->createQuery('select c from AcmtoolAppBundle:Project c
									WHERE c.owner = :customer and c.state= :state')
							->setParameter("customer",$customer)
							->setParameter("state",$state)
	                        ->setMaxResults(ConstValues::COUNT)
	                        ->setFirstResult($start)
	                        ->getResult();
        return $result;	
	}
	public function getProjectByLoggedUser($user,$display_id)
	{
		$project=null;
		if($user instanceOf Admin)
		{
			$project=$this->findOneBy(array("displayid"=>$display_id));
		}
		elseif ($user instanceOf Customer) {
			$project=$this->findOneBy(array("displayid"=>$display_id,"owner"=>$user));
		}
		elseif($user instanceOf CustomerUser){
			$project=$this->findOneBy(array("displayid"=>$display_id,"owner"=>$user->getCompany()));
		}
		elseif ($user instanceOf TeamLeader) {
			$project=$this->findOneBy(array("displayid"=>$display_id,"teamleader"=>$user->getCredentials()));
		}
		elseif ($user instanceOf KeyAccount) {
			$project=$this->findOneBy(array("displayid"=>$display_id,"keyaccount"=>$user));
		}
		elseif($user instanceOf Developer)
		{
			try{
			 $project = $this->createQueryBuilder('p')
                ->innerJoin('p.developers', 'd')
                ->where('d.id = :user_id and p.displayid= :display_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('display_id',$display_id)
                ->getQuery()->getSingleResult();
            }
             catch(\Doctrine\ORM\NoResultException $e) {
        		$project=null;
    		}
		}
		elseif($user instanceOf Designer)
		{
			try{
			 $project = $this->createQueryBuilder('p')
                ->innerJoin('p.designers', 'd')
                ->where('d.id = :user_id and p.displayid= :display_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('display_id',$display_id)
                ->getQuery()->getSingleResult();
            }
             catch(\Doctrine\ORM\NoResultException $e) {
        		$project=null;
    		}
		}
		elseif($user instanceOf Tester)
		{
			try{
			 $project = $this->createQueryBuilder('p')
                ->innerJoin('p.testers', 'd')
                ->where('d.id = :user_id and p.displayid= :display_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('display_id',$display_id)
                ->getQuery()->getSingleResult();
            }
             catch(\Doctrine\ORM\NoResultException $e) {
        		$project=null;
    		}
		}
		elseif($user instanceOf SystemAdmin)
		{
			try{
			 $project = $this->createQueryBuilder('p')
                ->innerJoin('p.sysadmins', 'd')
                ->where('d.id = :user_id and p.displayid= :display_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('display_id',$display_id)
                ->getQuery()->getSingleResult();
            }
             catch(\Doctrine\ORM\NoResultException $e) {
        		$project=null;
    		}
		}
		return $project;
	}
	public function getTotalProjectCount()
	{
		$em=$this->getEntityManager();
		$totalcount=$em->createQuery("SELECT COUNT(p) FROM AcmtoolAppBundle:Project p")
            ->getSingleScalarResult();
        return $totalcount;
	}
}

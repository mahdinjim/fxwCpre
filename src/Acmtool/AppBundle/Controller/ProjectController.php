<?php
namespace Acmtool\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Acmtool\AppBundle\Entity\Customer;
use Acmtool\AppBundle\Entity\Developer;
use Acmtool\AppBundle\Entity\Tester;
use Acmtool\AppBundle\Entity\Designer;
use Acmtool\AppBundle\Entity\SystemAdmin;
use Acmtool\AppBundle\Entity\TeamLeader;
use Acmtool\AppBundle\Entity\KeyAccount;
use Acmtool\AppBundle\Entity\Creds;
use Acmtool\AppBundle\Entity\Titles;
use Acmtool\AppBundle\Entity\ConstValues;
use Acmtool\AppBundle\Entity\Project;
use Acmtool\AppBundle\Entity\ProjectStates;

class ProjectController extends Controller
{
	public function createAction()
	{
		$request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
        	$KeyAccount=null;
        	$customer=null;
         	if($this->get('security.context')->isGranted("ROLE_ADMIN") || $this->get("security.context")->isGranted("ROLE_KEYACCOUNT"))
         	{
         		if(!isset($json->{"customer_id"}))
         		{
         			$response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
            		$response->headers->set('Content-Type', 'application/json');
            		return $response;
         		}
         		else
         		{
         			$customer=$em->getRepository("AcmtoolAppBundle:Customer")->findOneById($json->{"customer_id"});
         		}
         	}
         	else
         	{
         		$customer=$this->get("security.context")->getToken()->getUser();
         	}
         	if(!(isset($json->{'name'})))
            {
                $response=new Response('{"err":"name '.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            $project=new Project();
            $project->setState(ProjectStates::TLASSIGN);
            $project->setOwner($customer);
            $project->setKeyaccount($customer->getKeyAccount());
            $project->setName($json->{'name'});
            if(isset($json->{"skills"}))
            {
                $project->setProjectSkills($json->{"skills"});
            }
            if(isset($json->{"description"}))
            	$project->setDescription($json->{"description"});
            if(isset($json->{'startingdate'}))
            {
            	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$json->{'startingdate'}))
            	{
            		$format = 'Y-m-d';
					$startingdate = new \DateTime($json->{'startingdate'});
					$project->setStartingdate($startingdate);
            		
            	}
            	else{
            		$response=new Response('{"err":"'.ConstValues::INVALIDDATE.'"}',400);
                	$response->headers->set('Content-Type', 'application/json');
                	return $response;
            	}
            }
           
	    	if(isset($json->{"teamleader_id"}))
	    	{
	    		$project->setTeamleader($em->getRepository("AcmtoolAppBundle:TeamLeader")->findOneById($json->{"teamleader_id"}));
	    	}
	    	if(isset($json->{"developers"}))
	    	{
	    		foreach ($json->{"developers"} as $dev) {
                    $member=$em->getRepository("AcmtoolAppBundle:Developer")->findOneById($dev->{"id"});
	    			$project->addDeveloper($member);
                    $member->addProject($project);
	    		}
	    	}
            if(isset($json->{"testers"}))
            {
                foreach ($json->{"testers"} as $dev) {
                    $member=$em->getRepository("AcmtoolAppBundle:Tester")->findOneById($dev->{"id"});
                    $project->addTester($member);
                    $member->addProject($project);
                }
            }
            if(isset($json->{"designers"}))
            {
                foreach ($json->{"designers"} as $dev) {
                    $member=$em->getRepository("AcmtoolAppBundle:Designer")->findOneById($dev->{"id"});
                    $project->addDesigner($member);
                    $member->addProject($project);
                }
            }
            if(isset($json->{"sysadmins"}))
            {
                foreach ($json->{"sysadmins"} as $dev) {
                    $member=$em->getRepository("AcmtoolAppBundle:SystemAdmin")->findOneById($dev->{"id"});
                    $project->addSysAdmin($member);
                    $member->addProject($project);
                }
            }
            
            $chatservice=$this->get("acmtool_app.messaging");
            $chatprovider=$chatservice->CreateChatProvider();

            $result=$chatprovider->createGroupForProject(preg_replace('/\s+/', '_', $project->getName()));
            if($result["result"])
            {
                $project->setChannelid($result["id"]);
                $em->persist($project);
                $em->flush();
                $response=new Response(ConstValues::PROJECTCREATED,200);
                return $response;

            }
            else
            {
                $response=new Response($result["reason"],400);
                return $response;
            }
           


        }
	}
    public function updateAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            $project=null;
            if(!isset($json->{"project_id"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if((isset($json->{'name'})))
                {
                    $project->setName($json->{'name'});
                }
                if(isset($json->{"description"}))
                {
                    $project->setDescription($json->{"description"});
                }
                if(isset($json->{'startingdate'}))
                {
                    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$json->{'startingdate'}))
                    {
                        $format = 'Y-m-d';
                        $startingdate = new \DateTime($json->{'startingdate'});
                        $project->setStartingdate($startingdate);
                        
                    }
                    else{
                        $response=new Response('{"err":"'.ConstValues::INVALIDDATE.'"}',400);
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }
                }
                $em->flush();
                $res=new Response();
                $res->setStatusCode(200);
                $res->setContent(ConstValues::PROJECTUPDATED);
                return $res;
            }
        }
              
    }
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($id);
        if($project){
            $em->remove($project);
            $em->flush();
            $res=new Response();
            $res->setStatusCode(200);
            $res->setContent(ConstValues::PROJECTDELETED);
            return $res;
        }
        else
        {
            $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
    public function listAction($page,$state)
    {
        $em = $this->getDoctrine()->getManager();
        $result=null;
        $totalpages=null;
        if($this->get('security.context')->isGranted("ROLE_ADMIN"))
        {
            if($state==ProjectStates::ALL)
            {
                $totalpages=ceil($em->createQuery("SELECT COUNT(p) FROM AcmtoolAppBundle:Project p")
                ->getSingleScalarResult()/ConstValues::COUNT);
                $start=ConstValues::COUNT*($page-1);
                $result=$em->createQuery('select p from AcmtoolAppBundle:Project p')
                            ->setMaxResults(ConstValues::COUNT)
                            ->setFirstResult($start)
                            ->getResult();
            }
            else
            {
                $totalpages=ceil($em->createQuery("SELECT COUNT(p) FROM AcmtoolAppBundle:Project p WHERE p.state= :state")
                ->setParameter("state",$state)
                ->getSingleScalarResult()/ConstValues::COUNT);
                $start=ConstValues::COUNT*($page-1);
                $result=$em->createQuery('select p from AcmtoolAppBundle:Project p WHERE p.state= :state')
                            ->setParameter("state",$state)
                            ->setMaxResults(ConstValues::COUNT)
                            ->setFirstResult($start)
                            ->getResult();
            }
        }
        elseif ($this->get('security.context')->isGranted("ROLE_KEYACCOUNT")) {
            $keyaccount=$this->get("security.context")->getToken()->getUser();
            $totalpages=ceil($em->getRepository("AcmtoolAppBundle:Project")->getProjectsCountbyKeyAccount($keyaccount,$state)/ConstValues::COUNT);
            $start=ConstValues::COUNT*($page-1);
            $result=$em->getRepository("AcmtoolAppBundle:Project")->getProjectsByKeyAccount($keyaccount,$start,$state);
        }
        elseif($this->get('security.context')->isGranted("ROLE_CUSTOMER")) {
            $customer=$this->get("security.context")->getToken()->getUser();
            $totalpages=ceil($em->getRepository("AcmtoolAppBundle:Project")->getProjectCountByCustomer($customer,$state)/ConstValues::COUNT);
            $start=ConstValues::COUNT*($page-1);
            $result=$em->getRepository("AcmtoolAppBundle:Project")->getProjectsByCustomer($customer,$start,$state);
        }
        elseif($this->get('security.context')->isGranted("ROLE_CUSER"))
        {
            $customer=$this->get("security.context")->getToken()->getUser()->getCompany();
            $totalpages=ceil($em->getRepository("AcmtoolAppBundle:Project")->getProjectCountByCustomer($customer,$state)/ConstValues::COUNT);
            $start=ConstValues::COUNT*($page-1);
            $result=$em->getRepository("AcmtoolAppBundle:Project")->getProjectsByCustomer($customer,$start,$state);
        }
        elseif($this->get('security.context')->isGranted("ROLE_DEVELOPER")){
            $user_id=$this->get("security.context")->getToken()->getUser()->getId();
            $repository = $em->getRepository('AcmtoolAppBundle:Project');
            $query = $repository->createQueryBuilder('p')
                ->innerJoin('p.developers', 'd')
                ->where('d.id = :developer_id')
                ->setParameter('developer_id', $user_id)
                ->getQuery()->getResult();
        }
        else
        {
            $response=new Response(403);
            return $response;
        }
        if($result && $totalpages)
        {
            $mess=array();
            $mess['totalpages']=$totalpages;
            $projects=array();
            $channels=array();
            $i=0;
            foreach ($result as $key) {
                $projects[$i]=array("id"=>$key->getId(),"name"=>$key->getName(),"company"=>$key->getOwner()->getCompanyname());
                $channels[$i]=array("id"=>$key->getChannelid(),"name"=>$key->getName());
                $i++;
            }
            $mess["current_page"]=$page;
            $mess["projects"]=$projects;
            $mess["channels"]=$channels;
            $res=new Response();
            $res->setStatusCode(200);
            $res->setContent(json_encode($mess));
            return $res;


        }
        else
        {
            $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
    public function detailsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($id);
        if($project)
        {
            $mess=array("id"=>$project->getId(),"name"=>$project->getName(),"description"=>$project->getDescription(),"customer"=>$project->getOwner()->getCompanyname(),"state"=>$project->getState());
            $mess["keyaccount"]=array("id"=>$project->getKeyAccount()->getId(),"surname"=>$project->getKeyAccount()->getSurname(),"name"=>$project->getKeyAccount()->getName(),"email"=>$project->getKeyAccount()->getEmail(),"photo"=>$project->getKeyAccount()->getPhoto());
            if($project->getTeamleader())
                $mess["teamleader"]=array("id"=>$project->getTeamleader()->getId(),"surname"=>$project->getTeamleader()->getSurname(),"name"=>$project->getTeamleader()->getName(),"email"=>$project->getTeamleader()->getEmail(),"photo"=>$project->getTeamleader()->getPhoto());
            $i=0;
            $developers=array();
            foreach ($project->getDevelopers() as $key) {
                $developers[$i]=array("id"=>$key->getId(),"surname"=>$key->getSurname(),"name"=>$key->getName(),"email"=>$key->getEmail(),"photo"=>$key->getPhoto());
                $i++;
            }           
            $mess["developers"]=$developers;
            $i=0;
            $testers=array();
            foreach ($project->getTesters() as $key) {
                $testers[$i]=array("id"=>$key->getId(),"surname"=>$key->getSurname(),"name"=>$key->getName(),"email"=>$key->getEmail(),"photo"=>$key->getPhoto());
                $i++;
            }  
            $mess["testers"]=$testers;
            $designers=array();
            $i=0;
            foreach ($project->getDesigners() as $key) {
                $designers[$i]=array("id"=>$key->getId(),"surname"=>$key->getSurname(),"name"=>$key->getName(),"email"=>$key->getEmail(),"photo"=>$key->getPhoto());
                $i++;
            }  
            $mess["designers"]=$designers;
            $sysadmins=array();
            $i=0;
            foreach ($project->getSysadmins() as $key) {
                $sysadmins[$i]=array("id"=>$key->getId(),"surname"=>$key->getSurname(),"name"=>$key->getName(),"email"=>$key->getEmail(),"photo"=>$key->getPhoto());
                $i++;
            }  
            $mess["sysadmins"]=$sysadmins;
            $res=new Response();
            $res->setStatusCode(200);
            $res->setContent(json_encode($mess));
            $res->headers->set('Content-Type', 'application/json');
            return $res;

        }
        else
        {
            $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
    public function assignTeamLeaderAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"teamleader_id"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                $TeamLeader=$em->getRepository("AcmtoolAppBundle:TeamLeader")->findOneById($json->{"teamleader_id"});
                if($project && $TeamLeader)
                {
                    $project->setTeamleader($TeamLeader);
                    $project->setState(ProjectStates::TEAMASSIGN);
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::TEAMLEADERASSIGNED);
                    return $res;
                }
                else
                {
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }

            }
        }
    }
    public function addDeveloperAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"developers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {
                    foreach ($json->{"developers"} as $key) {
                       $member=$em->getRepository("AcmtoolAppBundle:Developer")->findOneById($key->{"id"});
                       if($member)
                       {
                            $project->addDeveloper($member);
                            $member->addProject($project);
                       }
                    }
                    
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERADDED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }

        }
    }
    public function addDesignerAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"designers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {
                    foreach ($json->{"designers"} as $key) {
                       $member=$em->getRepository("AcmtoolAppBundle:Designer")->findOneById($key->{"id"});
                       if($member)
                       {
                            $project->addDesigner($member);
                            $member->addProject($project);
                       }
                    }
                    
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERADDED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }

        }
    }
    public function addTesterAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"testers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {
                    foreach ($json->{"testers"} as $key) {
                       $member=$em->getRepository("AcmtoolAppBundle:Tester")->findOneById($key->{"id"});
                       if($member)
                       {
                            $project->addTester($member);
                            $member->addProject($project);
                       }
                    }
                    
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERADDED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }

        }
    }
    public function addSysadminAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"sysadmins"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {
                    foreach ($json->{"sysadmins"} as $key) {
                       $member=$em->getRepository("AcmtoolAppBundle:SystemAdmin")->findOneById($key->{"id"});
                       if($member)
                       {
                            $project->addSysAdmin($member);
                            $member->addProject($project);
                       }
                    }
                    
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERADDED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }

        }
    }
    public function deleteDeveloperAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"developers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {

                    foreach ($json->{"developers"} as $key) {
                        $member=$em->getRepository("AcmtoolAppBundle:Developer")->findOneById($key->{"id"});
                       if($member)
                       {
                        $project->removeDeveloper($member);
                        $member->removeProject($project);
                       }
                    }
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERDELETED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }
        }
    }
    public function assignBudgetAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"budget"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {

                    $project->setBudget($json->{"budget"});
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERDELETED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }
        }
    }
    public function deleteDesignerAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"designers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {

                    foreach ($json->{"designers"} as $key) {
                        $member=$em->getRepository("AcmtoolAppBundle:Designer")->findOneById($key->{"id"});
                       if($member)
                       {
                        $project->removeDesigner($member);
                        $member->removeProject($project);
                       }
                    }
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERDELETED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }
        }
    }
    public function deleteTesterAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"testers"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {

                    foreach ($json->{"testers"} as $key) {
                        $member=$em->getRepository("AcmtoolAppBundle:Tester")->findOneById($key->{"id"});
                       if($member)
                       {
                        $project->removeTester($member);
                        $member->removeProject($project);
                       }
                    }
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERDELETED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }
        }
    }
    public function deleteSysadminAction()
    {
        $request = $this->get('request');
        $message = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $result = $this->get('acmtool_app.validation.json')->validate($message);
        if(!$result["valid"])
            return $result['response'];
        else
        {
            $json=$result['json'];
            if(!isset($json->{"project_id"}) || !isset($json->{"sysadmins"}))
            {
                $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;       
            }
            else
            {
                $project=$em->getRepository("AcmtoolAppBundle:Project")->findOneById($json->{"project_id"});
                if($project)
                {

                    foreach ($json->{"sysadmins"} as $key) {
                        $member=$em->getRepository("AcmtoolAppBundle:SystemAdmin")->findOneById($key->{"id"});
                       if($member)
                       {
                        $project->removeSysadmin($member);
                        $member->removeProject($project);
                       }
                    }
                    $em->flush();
                    $res=new Response();
                    $res->setStatusCode(200);
                    $res->setContent(ConstValues::MEMBERDELETED);
                    return $res;
                }
                else
                {    
                    $response=new Response('{"err":"'.ConstValues::INVALIDREQUEST.'"}',400);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;  
                }
            }
        }
    }

}
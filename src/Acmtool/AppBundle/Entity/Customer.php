<?php

namespace Acmtool\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Customer
 * @UniqueEntity(fields={"email"},message="This email is already used")
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Acmtool\AppBundle\Entity\CustomerRepository")
 */
class Customer implements UserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
     /**
     * @var string
     * @Assert\NotBlank(message="The name field is required")
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message="The surname field is required")
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="telnumber", type="decimal",nullable=true)
     */
    private $telnumber;

    /**
     * @var string
     * @Assert\NotBlank(message="The companyname field is required")
     * @ORM\Column(name="companyname", type="string", length=255)
     */
    private $companyname;

    /**
     * @var integer
     *
     * @ORM\Column(name="vat", type="integer",nullable=true)
     */
    private $vat;
    /**
     * @var string
     * @Assert\NotBlank(message="The email field is required")
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.", checkMX = true, checkHost = true)
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;
    /**
     * @var string
     * @ORM\Column(name="logo", type="string", length=255,nullable=true)
     */
    private $logo;

    /**
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="Creds",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="cred_id", referencedColumnName="id")
     **/
    private $credentials;
    /**
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="Address",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     **/

    private $address;
      /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;
    /**
    * @ORM\OneToMany(targetEntity="CustomerUser", mappedBy="company")
    */
    private $users;
    /**
     * @ORM\OneToOne(targetEntity="Token")
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id",onDelete="SET NULL")
     **/
    private $apitoken;
    /**
    * @Assert\NotBlank
    * @ORM\ManyToOne(targetEntity="KeyAccount", inversedBy="cutomers")
    * @ORM\JoinColumn(name="keyaccount_id",referencedColumnName="id",onDelete="SET NULL")
    */
    private $keyAccount;
    /**
    * @ORM\OneToMany(targetEntity="Project", mappedBy="owner")
    */
    private $projects;
    /**
     * @var string
     *
     * @ORM\Column(name="month", type="string", length=255)
     */
    private $month;
    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=255)
     */
    private $year;
    /**
     * @var string
     *
     * @ORM\Column(name="day", type="string", length=255)
     */
    private $day;
    /**
     * @var string
     *
     * @ORM\Column(name="phonecode", type="string", length=255)
     */
    private $phonecode;
    public function __construct()
    {
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));
        $this->users = new ArrayCollection();
        $this->projects=new ArrayCollection();

    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
     /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }
    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->credentials->getLogin();
    }
    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->credentials->getPassword();
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array('ROLE_CUSTOMER');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Set companyname
     *
     * @param string $companyname
     * @return Customer
     */
    public function setCompanyname($companyname)
    {
        $this->companyname = $companyname;
    
        return $this;
    }

    /**
     * Get companyname
     *
     * @return string 
     */
    public function getCompanyname()
    {
        return $this->companyname;
    }

    /**
     * Set vat
     *
     * @param integer $vat
     * @return Customer
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return integer 
     */
    public function getVat()
    {
        return $this->vat;
    }
     /**
     * Set email
     *
     * @param string $email
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * Set Address
     *
     * @param Address address
     * @return Customer
     */
    public function setAddress($address)
    {
        $this->address=$address;
    }
    /**
     * Set Address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Customer
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Customer
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set credentials
     *
     * @param \Acmtool\AppBundle\Entity\Creds $credentials
     * @return Customer
     */
    public function setCredentials(\Acmtool\AppBundle\Entity\Creds $credentials = null)
    {
        $this->credentials = $credentials;
    
        return $this;
    }

    /**
     * Get credentials
     *
     * @return \Acmtool\AppBundle\Entity\Creds 
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Add users
     *
     * @param \Acmtool\AppBundle\Entity\CustomerUser $users
     * @return Customer
     */
    public function addUser(\Acmtool\AppBundle\Entity\CustomerUser $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Acmtool\AppBundle\Entity\CustpmerUser $users
     */
    public function removeUser(\Acmtool\AppBundle\Entity\CustomerUser $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set apitoken
     *
     * @param \Acmtool\AppBundle\Entity\Token $apitoken
     * @return Customer
     */
    public function setApitoken(\Acmtool\AppBundle\Entity\Token $apitoken = null)
    {
        $this->apitoken = $apitoken;
    
        return $this;
    }

    /**
     * Get apitoken
     *
     * @return \Acmtool\AppBundle\Entity\Token 
     */
    public function getApitoken()
    {
        return $this->apitoken;
    }
    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
        ) = unserialize($serialized);
    }
     /**
     * Set name
     *
     * @param string $name
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return Customer
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    
        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set telnumber
     *
     * @param string $telnumber
     * @return Customer
     */
    public function setTelnumber($telnumber)
    {
        $this->telnumber = $telnumber;
    
        return $this;
    }

    /**
     * Get telnumber
     *
     * @return string 
     */
    public function getTelnumber()
    {
        return $this->telnumber;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Customer
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    
        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set KeyAccount
     *
     * @param \Acmtool\AppBundle\Entity\KeyAccount $keyAccount
     * @return Customer
     */
    public function setKeyAccount(\Acmtool\AppBundle\Entity\KeyAccount $keyAccount = null)
    {
        $this->KeyAccount = $keyAccount;
    
        return $this;
    }

    /**
     * Get KeyAccount
     *
     * @return \Acmtool\AppBundle\Entity\KeyAccount 
     */
    public function getKeyAccount()
    {
        return $this->KeyAccount;
    }

    /**
     * Add projects
     *
     * @param \Acmtool\AppBundle\Entity\Project $projects
     * @return Customer
     */
    public function addProject(\Acmtool\AppBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;
    
        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Acmtool\AppBundle\Entity\Project $projects
     */
    public function removeProject(\Acmtool\AppBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set month
     *
     * @param string $month
     * @return Customer
     */
    public function setMonth($month)
    {
        $this->month = $month;
    
        return $this;
    }

    /**
     * Get month
     *
     * @return string 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param string $year
     * @return Customer
     */
    public function setYear($year)
    {
        $this->year = $year;
    
        return $this;
    }

    /**
     * Get year
     *
     * @return string 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set day
     *
     * @param string $day
     * @return Customer
     */
    public function setDay($day)
    {
        $this->day = $day;
    
        return $this;
    }

    /**
     * Get day
     *
     * @return string 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set phonecode
     *
     * @param string $phonecode
     * @return Customer
     */
    public function setPhonecode($phonecode)
    {
        $this->phonecode = $phonecode;
    
        return $this;
    }

    /**
     * Get phonecode
     *
     * @return string 
     */
    public function getPhonecode()
    {
        return $this->phonecode;
    }
}
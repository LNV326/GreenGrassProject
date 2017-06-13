<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="forum_sessions")
 */
class Session
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="string", length=32)
	 */
	protected $id;
	/**
	 * @ORM\Column(name="member_name", type="string", length=64)
	 */
	protected $username;
	/**
	 * @ORM\Column(name="member_id", type="integer", length=8)
	 */
	protected $userId;
	/**
	 * @ORM\Column(name="ip_adress", type="string", length=16)
	 */
	protected $IP;
	/**
	 * @ORM\Column(name="browser", type="string", length=64)
	 */
	protected $browser;
	/**
	 * @ORM\Column(name="running_time", type="integer", length=10)
	 */
	protected $created;
	/**
	 * @ORM\Column(name="login_type", type="integer", length=1)
	 */
	protected $loginType;
	/**
	 * @ORM\Column(name="location", type="text")
	 */
	protected $location;
	/**
	 * @ORM\Column(name="member_group", type="integer", length=3)
	 */
	protected $userGroupId;
	/**
	 * @ORM\Column(name="in_forum", type="integer", length=5)
	 */
	protected $forumId;
	/**
	 * @ORM\Column(name="in_topic", type="integer", length=10)
	 */
	protected $topicId;

    /**
     * Set id
     *
     * @param string $id
     * @return Session
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Session
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Session
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set IP
     *
     * @param string $iP
     * @return Session
     */
    public function setIP($iP)
    {
        $this->IP = $iP;
    
        return $this;
    }

    /**
     * Get IP
     *
     * @return string 
     */
    public function getIP()
    {
        return $this->IP;
    }

    /**
     * Set browser
     *
     * @param string $browser
     * @return Session
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    
        return $this;
    }

    /**
     * Get browser
     *
     * @return string 
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Set created
     *
     * @param integer $created
     * @return Session
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set loginType
     *
     * @param integer $loginType
     * @return Session
     */
    public function setLoginType($loginType)
    {
        $this->loginType = $loginType;
    
        return $this;
    }

    /**
     * Get loginType
     *
     * @return integer 
     */
    public function getLoginType()
    {
        return $this->loginType;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Session
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set userGroupId
     *
     * @param integer $userGroupId
     * @return Session
     */
    public function setUserGroupId($userGroupId)
    {
        $this->userGroupId = $userGroupId;
    
        return $this;
    }

    /**
     * Get userGroupId
     *
     * @return integer 
     */
    public function getUserGroupId()
    {
        return $this->userGroupId;
    }

    /**
     * Set forumId
     *
     * @param integer $forumId
     * @return Session
     */
    public function setForumId($forumId)
    {
        $this->forumId = $forumId;
    
        return $this;
    }

    /**
     * Get forumId
     *
     * @return integer 
     */
    public function getForumId()
    {
        return $this->forumId;
    }

    /**
     * Set topicId
     *
     * @param integer $topicId
     * @return Session
     */
    public function setTopicId($topicId)
    {
        $this->topicId = $topicId;
    
        return $this;
    }

    /**
     * Get topicId
     *
     * @return integer 
     */
    public function getTopicId()
    {
        return $this->topicId;
    }
}
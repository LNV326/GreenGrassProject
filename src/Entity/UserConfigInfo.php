<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Site\CoreBundle\Entity\UserFullInfo;

/**
 * @ORM\Entity
 * @ORM\Table(name="forum_members")
 */
class UserConfigInfo implements UserInterface
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", length=8)
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	protected $username;
	/**
	 * @ORM\Column(name="password", type="string", length=32)
	 */
	protected $passhash;
	/**
	 * @ORM\Column(name="mgroup", type="integer", length=3)
	 */
	protected $groupId;
	/**
	 * @ORM\Column(name="time_offset", type="string", length=10)
	 */
	protected $timeOffset;
	/**
	 * @ORM\Column(name="skin", type="integer", length=5)
	 */
	protected $skin;
	/**
	 * @ORM\Column(name="language", type="string", length=32)
	 */
	protected $language;
	/**
	 * @ORM\Column(name="view_sigs", type="boolean")
	 */
	protected $view_sigs;
	/**
	 * @ORM\Column(name="view_winter", type="boolean")
	 */
	protected $view_winter;
	/**
	 * @ORM\Column(name="view_bar", type="boolean")
	 */
	protected $view_bar;
	/**
	 * @ORM\Column(name="view_img", type="boolean")
	 */
	protected $view_img;
	/**
	 * @ORM\Column(name="view_avs", type="boolean")
	 */
	protected $view_avs;
	/**
	 * @ORM\Column(name="view_pop", type="boolean")
	 */
	protected $view_pop;
	/**
	 * @ORM\Column(name="view_quk", type="boolean")
	 */
	protected $view_quk;
	/**
	 * @ORM\Column(name="view_wlp", type="boolean")
	 */
	protected $view_wlp;
	/**
	 * @ORM\Column(name="bday_day", type="integer", length=2)
	 */
	protected $bday_day;
	/**
	 * @ORM\Column(name="bday_month", type="integer", length=2)
	 */
	protected $bday_month;
	/**
	 * @ORM\Column(name="bday_year", type="integer", length=4)
	 */
	protected $bday_year;
	/**
	 * @ORM\Column(name="last_visit", type="integer", length=10)
	 */
	protected $last_visit;
	/**
	 * @ORM\Column(name="last_activity", type="integer", length=10)
	 */
	protected $last_activity;
	/**
	 * @ORM\Column(name="posts", type="integer", length=7)
	 */
	protected $postsCount;
	/**
	 * @ORM\Column(name="posts_bad", type="integer", length=7)
	 */
	protected $postsBadCount;
// 	/**
// 	 * @ORM\OneToOne(targetEntity="UserFullInfo")
// 	 * @ORM\JoinColumn(name="id", referencedColumnName="id")
// 	 **/
// 	protected $userFullInfo;
	
	public function getUsername() {
		return $this->username;
	}
	public function getPassword() {
		return $this->passhash;
	}
	public function getSalt() {
		return '';
	}
	public function getRoles() {
		$roles = array('ROLE_GAL_SHOW');
		if ($this->id == 0) {
			$roles[] = 'ROLE_GUEST';
		} else {
			$roles[] = 'ROLE_USER';
			if (in_array($this->groupId, array(4,9,10))) {
				$roles[] = 'ROLE_GAL_ADD_IMG';
				$roles[] = 'ROLE_GAL_ADD_ALB';
				$roles[] = 'ROLE_GAL_EDIT_IMG';
				$roles[] = 'ROLE_GAL_EDIT_ALB';
				$roles[] = 'ROLE_GAL_EDIT_CAT';
				$roles[] = 'ROLE_GAL_DEL_IMG';
			}
		}	
		return $roles;
	}
	public function eraseCredentials()
	{
		
	}
	
	public function isMod() {
		if ( in_array($this->groupId, array(4,9,10)) )
			return true;
		else return false;
	}

	//==============================
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
     * Set username
     *
     * @param string $username
     * @return UserConfigInfo
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Set passhash
     *
     * @param string $passhash
     * @return UserConfigInfo
     */
    public function setPasshash($passhash)
    {
        $this->passhash = $passhash;
    
        return $this;
    }

    /**
     * Get passhash
     *
     * @return string 
     */
    public function getPasshash()
    {
        return $this->passhash;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     * @return UserConfigInfo
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    
        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set timeOffset
     *
     * @param string $timeOffset
     * @return UserConfigInfo
     */
    public function setTimeOffset($timeOffset)
    {
        $this->timeOffset = $timeOffset;
    
        return $this;
    }

    /**
     * Get timeOffset
     *
     * @return string 
     */
    public function getTimeOffset()
    {
        return $this->timeOffset;
    }

    /**
     * Set skin
     *
     * @param integer $skin
     * @return UserConfigInfo
     */
    public function setSkin($skin)
    {
        $this->skin = $skin;
    
        return $this;
    }

    /**
     * Get skin
     *
     * @return integer 
     */
    public function getSkin()
    {
        return $this->skin;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return UserConfigInfo
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    
        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set view_sigs
     *
     * @param boolean $viewSigs
     * @return UserConfigInfo
     */
    public function setViewSigs($viewSigs)
    {
        $this->view_sigs = $viewSigs;
    
        return $this;
    }

    /**
     * Get view_sigs
     *
     * @return boolean 
     */
    public function getViewSigs()
    {
        return $this->view_sigs;
    }

    /**
     * Set view_winter
     *
     * @param boolean $viewWinter
     * @return UserConfigInfo
     */
    public function setViewWinter($viewWinter)
    {
        $this->view_winter = $viewWinter;
    
        return $this;
    }

    /**
     * Get view_winter
     *
     * @return boolean 
     */
    public function getViewWinter()
    {
        return $this->view_winter;
    }

    /**
     * Set view_bar
     *
     * @param boolean $viewBar
     * @return UserConfigInfo
     */
    public function setViewBar($viewBar)
    {
        $this->view_bar = $viewBar;
    
        return $this;
    }

    /**
     * Get view_bar
     *
     * @return boolean 
     */
    public function getViewBar()
    {
        return $this->view_bar;
    }

    /**
     * Set view_img
     *
     * @param boolean $viewImg
     * @return UserConfigInfo
     */
    public function setViewImg($viewImg)
    {
        $this->view_img = $viewImg;
    
        return $this;
    }

    /**
     * Get view_img
     *
     * @return boolean 
     */
    public function getViewImg()
    {
        return $this->view_img;
    }

    /**
     * Set view_avs
     *
     * @param boolean $viewAvs
     * @return UserConfigInfo
     */
    public function setViewAvs($viewAvs)
    {
        $this->view_avs = $viewAvs;
    
        return $this;
    }

    /**
     * Get view_avs
     *
     * @return boolean 
     */
    public function getViewAvs()
    {
        return $this->view_avs;
    }

    /**
     * Set view_pop
     *
     * @param boolean $viewPop
     * @return UserConfigInfo
     */
    public function setViewPop($viewPop)
    {
        $this->view_pop = $viewPop;
    
        return $this;
    }

    /**
     * Get view_pop
     *
     * @return boolean 
     */
    public function getViewPop()
    {
        return $this->view_pop;
    }

    /**
     * Set view_quk
     *
     * @param boolean $viewQuk
     * @return UserConfigInfo
     */
    public function setViewQuk($viewQuk)
    {
        $this->view_quk = $viewQuk;
    
        return $this;
    }

    /**
     * Get view_quk
     *
     * @return boolean 
     */
    public function getViewQuk()
    {
        return $this->view_quk;
    }

    /**
     * Set view_wlp
     *
     * @param boolean $viewWlp
     * @return UserConfigInfo
     */
    public function setViewWlp($viewWlp)
    {
        $this->view_wlp = $viewWlp;
    
        return $this;
    }

    /**
     * Get view_wlp
     *
     * @return boolean 
     */
    public function getViewWlp()
    {
        return $this->view_wlp;
    }

    /**
     * Set bday_day
     *
     * @param integer $bdayDay
     * @return UserConfigInfo
     */
    public function setBdayDay($bdayDay)
    {
        $this->bday_day = $bdayDay;
    
        return $this;
    }

    /**
     * Get bday_day
     *
     * @return integer 
     */
    public function getBdayDay()
    {
        return $this->bday_day;
    }

    /**
     * Set bday_month
     *
     * @param integer $bdayMonth
     * @return UserConfigInfo
     */
    public function setBdayMonth($bdayMonth)
    {
        $this->bday_month = $bdayMonth;
    
        return $this;
    }

    /**
     * Get bday_month
     *
     * @return integer 
     */
    public function getBdayMonth()
    {
        return $this->bday_month;
    }

    /**
     * Set bday_year
     *
     * @param integer $bdayYear
     * @return UserConfigInfo
     */
    public function setBdayYear($bdayYear)
    {
        $this->bday_year = $bdayYear;
    
        return $this;
    }

    /**
     * Get bday_year
     *
     * @return integer 
     */
    public function getBdayYear()
    {
        return $this->bday_year;
    }

    /**
     * Set last_visit
     *
     * @param integer $lastVisit
     * @return UserConfigInfo
     */
    public function setLastVisit($lastVisit)
    {
        $this->last_visit = $lastVisit;
    
        return $this;
    }

    /**
     * Get last_visit
     *
     * @return integer 
     */
    public function getLastVisit()
    {
        return $this->last_visit;
    }

    /**
     * Set last_activity
     *
     * @param integer $lastActivity
     * @return UserConfigInfo
     */
    public function setLastActivity($lastActivity)
    {
        $this->last_activity = $lastActivity;
    
        return $this;
    }

    /**
     * Get last_activity
     *
     * @return integer 
     */
    public function getLastActivity()
    {
        return $this->last_activity;
    }
    
    /**
     * Set postsCount
     *
     * @param integer $postsCount
     * @return UserConfigInfo
     */
    public function setPostsCount($postsCount)
    {
    	$this->postsCount = postsCount;
    
    	return $this;
    }
    
    /**
     * Get postsCount
     *
     * @return integer
     */
    public function getPostsCount()
    {
    	return $this->postsCount;
    }
//     /**
//      * Set userFullInfo
//      *
//      * @param \Site\CoreBundle\Entity\UserFullInfo $userFullInfo
//      * @return UserConfigInfo
//      */
//     public function setUserFullInfo(\Site\CoreBundle\Entity\UserFullInfo $userFullInfo = null)
//     {
//         $this->userFullInfo = $userFullInfo;
    
//         return $this;
//     }

//     /**
//      * Get userFullInfo
//      *
//      * @return \Site\CoreBundle\Entity\UserFullInfo 
//      */
//     public function getUserFullInfo()
//     {
//         return $this->userFullInfo;
//     }

    /**
     * Set postsBadCount
     *
     * @param integer $postsBadCount
     * @return UserConfigInfo
     */
    public function setPostsBadCount($postsBadCount)
    {
        $this->postsBadCount = $postsBadCount;
    
        return $this;
    }

    /**
     * Get postsBadCount
     *
     * @return integer 
     */
    public function getPostsBadCount()
    {
        return $this->postsBadCount;
    }
}
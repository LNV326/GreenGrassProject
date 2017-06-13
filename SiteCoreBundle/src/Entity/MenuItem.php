<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_menu_items")
 */
class MenuItem
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", length=8)
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="cat_id", type="integer", length=10)
	 */
	protected $categoryId;	
	/**
	 * @ORM\Column(name="poz", type="integer", length=2)
	 */
	protected $position;
	/**
	 * @ORM\Column(name="info", type="string", length=20)
	 */
	protected $name;
	/**
	 * @ORM\Column(name="type", type="string", length=10)
	 */
	protected $isLink;
	/**
	 * @ORM\Column(name="url", type="boolean")
	 */
	protected $linkUrl;
	/**
	 * @ORM\Column(name="new", type="boolean")
	 */
	protected $new;
	/**
	 * @ORM\Column(name="open_new", type="boolean")
	 */
	protected $openNew;
	/**
	 * @ORM\ManyToOne(targetEntity="MenuCategory", inversedBy="menuItems")
	 * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
	 **/
	protected $menuCategory;

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
     * Set categoryId
     *
     * @param integer $categoryId
     * @return MenuItem
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    
        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer 
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return MenuItem
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MenuItem
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
     * Set isLink
     *
     * @param string $isLink
     * @return MenuItem
     */
    public function setIsLink($isLink)
    {
        $this->isLink = $isLink;
    
        return $this;
    }

    /**
     * Get isLink
     *
     * @return string 
     */
    public function getIsLink()
    {
        return $this->isLink;
    }

    /**
     * Set linkUrl
     *
     * @param boolean $linkUrl
     * @return MenuItem
     */
    public function setLinkUrl($linkUrl)
    {
        $this->linkUrl = $linkUrl;
    
        return $this;
    }

    /**
     * Get linkUrl
     *
     * @return boolean 
     */
    public function getLinkUrl()
    {
        return $this->linkUrl;
    }

    /**
     * Set new
     *
     * @param boolean $new
     * @return MenuItem
     */
    public function setNew($new)
    {
        $this->new = $new;
    
        return $this;
    }

    /**
     * Get new
     *
     * @return boolean 
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * Set openNew
     *
     * @param boolean $openNew
     * @return MenuItem
     */
    public function setOpenNew($openNew)
    {
        $this->openNew = $openNew;
    
        return $this;
    }

    /**
     * Get openNew
     *
     * @return boolean 
     */
    public function getOpenNew()
    {
        return $this->openNew;
    }

    /**
     * Set menuCategory
     *
     * @param \Site\CoreBundle\Entity\MenuCategory $menuCategory
     * @return MenuItem
     */
    public function setMenuCategory(\Site\CoreBundle\Entity\MenuCategory $menuCategory = null)
    {
        $this->menuCategory = $menuCategory;
    
        return $this;
    }

    /**
     * Get menuCategory
     *
     * @return \Site\CoreBundle\Entity\MenuCategory 
     */
    public function getMenuCategory()
    {
        return $this->menuCategory;
    }
}
<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_menu_cat")
 */
class MenuCategory
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", length=5)
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="poz", type="integer", length=5)
	 */
	protected $position;
	/**
	 * @ORM\Column(name="name", type="string", length=20)
	 */
	protected $name;
	/**
	 * ORM\Column(name="era", type="integer", length=1)
	 */
	protected $era;
	/**
	 * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="menuCategory")
	 **/
	protected $menuItems;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuItems = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set position
     *
     * @param integer $position
     * @return MenuCategory
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
     * @return MenuCategory
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
     * Set era
     *
     * @param integer $era
     * @return MenuCategory
     */
    public function setEra($era)
    {
        $this->era = $era;
    
        return $this;
    }

    /**
     * Get era
     *
     * @return integer 
     */
    public function getEra()
    {
        return $this->era;
    }

    /**
     * Add menuItems
     *
     * @param \Site\CoreBundle\Entity\MenuItem $menuItems
     * @return MenuCategory
     */
    public function addMenuItem(\Site\CoreBundle\Entity\MenuItem $menuItems)
    {
        $this->menuItems[] = $menuItems;
    
        return $this;
    }

    /**
     * Remove menuItems
     *
     * @param \Site\CoreBundle\Entity\MenuItem $menuItems
     */
    public function removeMenuItem(\Site\CoreBundle\Entity\MenuItem $menuItems)
    {
        $this->menuItems->removeElement($menuItems);
    }

    /**
     * Get menuItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }
}
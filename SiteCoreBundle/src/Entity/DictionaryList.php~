<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Site\CoreBundle\Repository\DictionaryListRep")
 * @ORM\Table(name="directory_list")
 */
class DictionaryList
{
	/**
	 * @ORM\Id
	 * @ORM\Column(name="objid",type="integer", length=8, options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $objId;
	/**
	 * @ORM\Column(name="category", type="string", length=15)
	 */
	protected $categoryName;
	/**
	 * @ORM\Column(name="refid", type="string", length=15)
	 */
	protected $referenceName;
	/**
	 * @ORM\Column(name="title", type="string", length=30)
	 */
	protected $title;

	// Связи

	/**
	 * @ORM\OneToMany(targetEntity="DictionaryItem", mappedBy="list")
	 **/
	protected $items;
	
	public function __construct() {
		$this->items = new ArrayCollection();
	}

    /**
     * Get objId
     *
     * @return integer 
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set categoryName
     *
     * @param string $categoryName
     * @return DictionaryList
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
    
        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string 
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set referenceName
     *
     * @param string $referenceName
     * @return DictionaryList
     */
    public function setReferenceName($referenceName)
    {
        $this->referenceName = $referenceName;
    
        return $this;
    }

    /**
     * Get referenceName
     *
     * @return string 
     */
    public function getReferenceName()
    {
        return $this->referenceName;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return DictionaryList
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add items
     *
     * @param \Site\CoreBundle\Entity\DictionaryItem $items
     * @return DictionaryList
     */
    public function addItem(\Site\CoreBundle\Entity\DictionaryItem $items)
    {
        $this->items[] = $items;
    
        return $this;
    }

    /**
     * Remove items
     *
     * @param \Site\CoreBundle\Entity\DictionaryItem $items
     */
    public function removeItem(\Site\CoreBundle\Entity\DictionaryItem $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }
}
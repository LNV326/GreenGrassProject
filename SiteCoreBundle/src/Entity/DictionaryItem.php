<?php
namespace Site\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Site\CoreBundle\Repository\DictionaryItemRep")
 * @ORM\Table(name="directory_elm")
 */
class DictionaryItem
{
	/**
	 * @ORM\Id
	 * @ORM\Column(name="objid",type="integer", length=8, options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $objId;
	/**
	 * @ORM\Column(name="refid", type="string", length=15)
	 */
	protected $refId;
	/**
	 * @ORM\Column(name="title", type="string", length=30)
	 */
	protected $title;
	
	// Связи
	
	/**
	 * @ORM\ManyToOne(targetEntity="DictionaryList", inversedBy="items")
	 * @ORM\JoinColumn(name="dir_elm2dir_list", referencedColumnName="objid")
	 **/
	protected $list;
	/**
	 * @ORM\OneToMany(targetEntity="\Site\GalleryBundle\Entity\ImageAlbum", mappedBy="dictionary")
	 */
	protected $album;

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
     * Set title
     *
     * @param string $title
     * @return DictionaryItem
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
     * Set list
     *
     * @param \Site\CoreBundle\Entity\DictionaryList $list
     * @return DictionaryItem
     */
    public function setList(\Site\CoreBundle\Entity\DictionaryList $list = null)
    {
        $this->list = $list;
    
        return $this;
    }

    /**
     * Get list
     *
     * @return \Site\CoreBundle\Entity\DictionaryList 
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Set album
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $album
     * @return DictionaryItem
     */
    public function setAlbum(\Site\GalleryBundle\Entity\ImageAlbum $album = null)
    {
        $this->album = $album;
    
        return $this;
    }

    /**
     * Get album
     *
     * @return \Site\GalleryBundle\Entity\ImageAlbum
     */
    public function getAlbum()
    {
        return $this->album;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->album = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set refId
     *
     * @param string $refId
     * @return DictionaryItem
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    
        return $this;
    }

    /**
     * Get refId
     *
     * @return string 
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Add album
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $album
     * @return DictionaryItem
     */
    public function addAlbum(\Site\GalleryBundle\Entity\ImageAlbum $album)
    {
        $this->album[] = $album;
    
        return $this;
    }

    /**
     * Remove album
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $album
     */
    public function removeAlbum(\Site\GalleryBundle\Entity\ImageAlbum $album)
    {
        $this->album->removeElement($album);
    }
}
<?php
namespace Site\GalleryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Site\GalleryBundle\Repository\ImageCategoryRep")
 * @ORM\Table(name="s_gallery_cat")
 * @ORM\HasLifecycleCallbacks
 */
class ImageCategory {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="name", type="string", length=32)
	 */
	protected $name;
// 	/**
// 	 * @ORM\Column(name="info", type="text")
// 	 */
// 	protected $info;
	/**
	 * @ORM\Column(name="poz", type="integer", length=10)
	 */
	protected $position;
	/**
	 * @ORM\Column(name="dir_name", type="string", length=25)
	 */
	protected $dirName;
	
	// ===== ����� =====
	/**
	 * @ORM\OneToOne(targetEntity="Image")
	 * @ORM\JoinColumn(name="default_img", referencedColumnName="id")
	 **/
	protected $coverImage;
    /**
     * @ORM\OneToMany(targetEntity="ImageAlbum", mappedBy="category", cascade={"remove"})
     **/
	protected $albums;
	
	protected $refid;
	
	// ===== ������ ������� =====
	
	public function __construct() {
		$this->subCategories = new ArrayCollection();
		// TODO ������
		$this->position = 21;
	}
	
	public function getAbsoluteDir() {
		return Image::MAIN_UPLOAD_DIR.$this->dirName."/";
	}
	
	/**
	 * @ORM\PrePersist()
	 * �������� ����� �������� ������ � ���� ������
	 */
	public function createDir() {
		// �������� ����� ��� �������
		if (!is_dir($path = $this->getAbsoluteDir())) {
			mkdir($path, 0777);
		};
	}
	
	/**
	 * @ORM\PreRemove()
	 * �������� ��� �������� ������ �� ���� ������
	 */
	public function removeDir() {
		if (is_dir($path = $this->getAbsoluteDir())) {
			rmdir($path);
		}
	}
	
	// ===== �������������� =====


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
     * Set name
     *
     * @param string $name
     * @return ImageCategory
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
     * Set position
     *
     * @param integer $position
     * @return ImageCategory
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
     * Set dirName
     *
     * @param string $dirName
     * @return ImageCategory
     */
    public function setDirName($dirName)
    {
        $this->dirName = $dirName;
    
        return $this;
    }

    /**
     * Get dirName
     *
     * @return string 
     */
    public function getDirName()
    {
        return $this->dirName;
    }

    /**
     * Set coverImage
     *
     * @param \Site\GalleryBundle\Entity\Image $coverImage
     * @return ImageCategory
     */
    public function setCoverImage(\Site\GalleryBundle\Entity\Image $coverImage = null)
    {
        $this->coverImage = $coverImage;
    
        return $this;
    }

    /**
     * Get coverImage
     *
     * @return \Site\GalleryBundle\Entity\Image 
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * Add albums
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $albums
     * @return ImageCategory
     */
    public function addAlbum(\Site\GalleryBundle\Entity\ImageAlbum $albums)
    {
        $this->albums[] = $albums;
    
        return $this;
    }

    /**
     * Remove albums
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $albums
     */
    public function removeAlbum(\Site\GalleryBundle\Entity\ImageAlbum $albums)
    {
        $this->albums->removeElement($albums);
    }

    /**
     * Get albums
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlbums()
    {
        return $this->albums;
    }
    
    public function getRefId()
    {
    	switch ($this->id) {
			case 0: return 'carbon';
			case 1: return 'most_wanted';
			case 2: return 'underground2';
			case 3: return 'underground';
			case 4: return 'hot_pursuit2';
			case 5: return 'porsche';
			case 6: return 'high_stakes';
			case 7: return 'hot_pursuit';
			case 8: return 'two';
			case 9: return 'road_and_track';
			case 10: return 'prostreet';
			case 11: return 'users';
			case 12: return 'undercover';
			case 13: return 'shift';
			case 14: return 'world_online';
			case 15: return 'hot_pursuit_cg';
			case 16: return 'shift2';
			case 17: return 'the_run';
			case 18: return 'motor_city_online';
			case 19: return 'most_wanted_cg';		
			case 20: return 'rivals';
		}
    }
    static public function getIdFromRefId($cRefId) {
    	switch ($cRefId) {
    		case 'carbon': return 0;
    		case 'most_wanted': return 1;
    		case 'underground2': return 2;
    		case 'underground': return 3;
    		case 'hot_pursuit2': return 4;
    		case 'porsche': return 5;
    		case 'high_stakes': return 6;
    		case 'hot_pursuit': return 7;
    		case 'two': return 8;
    		case 'road_and_track': return 9;
    		case 'prostreet': return 10;
    		case 'users': return 11;
    		case 'undercover': return 12;
    		case 'shift': return 13;
    		case 'world_online': return 14;
    		case 'hot_pursuit_cg': return 15;
    		case 'shift2': return 16;
    		case 'the_run': return 17;
    		case 'motor_city_online': return 18;
    		case 'most_wanted_cg': return 19;
    		case 'rivals': return 20;
    	}
    }
    
}
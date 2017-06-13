<?php
namespace Site\GalleryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Site\CoreBundle\Entity\DictionaryItem as DictionaryItem;

/**
 * @ORM\Entity(repositoryClass="Site\GalleryBundle\Repository\ImageAlbumRep")
 * @ORM\Table(name="s_gallery_subcat")
 * @ORM\HasLifecycleCallbacks
 */
class ImageAlbum {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="name", type="text")
	 */
	protected $name;
	/**
	 * @ORM\Column(name="cat_id", type="integer", options={"unsigned"=true})
	 */
	protected $categoryId;
// 	/**
// 	 * @ORM\Column(name="in_sub_id", type="integer", length=10)
// 	 */
// 	protected $parentSubCategoryId;
// 	/**
// 	 * @ORM\Column(name="sub", type="integer", length=10)
// 	 */
// 	protected $haveSubCategory;
// 	/**
// 	 * @ORM\Column(name="in_sub", type="boolean")
// 	 */
// 	protected $inSubCategory;
// 	/**
// 	 * @ORM\Column(name="info", type="text")
// 	 */
// 	protected $info;
	/**
	 * @ORM\Column(name="add_files", type="integer")
	 */
	protected $allowAdd;
	/**
	 * @ORM\Column(name="dir_name", type="string", length=25)
	 */
	protected $dirName;
// 	/**
// 	 * @ORM\Column(name="img_w", type="integer", length=5)
// 	 */
// 	protected $imageWidth;
// 	/**
// 	 * @ORM\Column(name="img_h", type="integer", length=5)
// 	 */
// 	protected $imageHeight;
// 	/**
// 	 * @ORM\Column(name="poz", type="integer", length=10)
// 	 */
// 	protected $position;
// 	/**
// 	 * @ORM\Column(name="sub_cnt", type="integer", length=10)
// 	 */
// 	protected $SubCategoryCount;
	/**
	 * @ORM\Column(name="album2dir_elm", type="integer", options={"unsigned"=true})
	 */
	protected $dictId;

	/**
	 * ORM\Column(name="status", type="string")
	 * ��� ENUM ����, �� � Doctrine ������ ��� =P
	 */
	protected $status;

	// ===== ����� =====
	
	/**
	 * @ORM\OneToOne(targetEntity="Image")
	 * @ORM\JoinColumn(name="default_img", referencedColumnName="id")
	 **/
	protected $coverImage;
	/**
	 * @ORM\ManyToOne(targetEntity="ImageCategory", inversedBy="albums")
	 * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
	 **/
	protected $category;	
	/**
	 * @ORM\OneToMany(targetEntity="Image", mappedBy="album", cascade={"remove"})
	 **/
	protected $images;
	/**
	 * @ORM\ManyToOne(targetEntity="\Site\CoreBundle\Entity\DictionaryItem", inversedBy="album")
	 * @ORM\JoinColumn(name="album2dir_elm", referencedColumnName="objid")
	 */
	private $dictionary;
	
	// ===== ������ ������� =====
		
	public function __construct() {
		$this->images = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getAbsoluteDir() {
		return Image::MAIN_UPLOAD_DIR.$this->dirName."/";
	}
	
	public function getAbsoluteThumbDir() {
		return $this->getAbsoluteDir().Image::THUMBNAILS_DIR;
	}
	
	public function getAbsoluteThumbDir_old() {
		return $this->getAbsoluteDir().Image::THUMBNAILS_DIR_OLD;
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
		if (!is_dir($path = $this->getAbsoluteThumbDir())) {
			mkdir($path, 0777);
		};
		if (!is_dir($path = $this->getAbsoluteThumbDir_old())) {
			mkdir($path, 0777);
		};
	}
	
	/**
	 * @ORM\PreRemove()
	 * �������� ��� �������� ������ �� ���� ������
	 */
	public function removeDir() {
		if (is_dir($path = $this->getAbsoluteThumbDir())) {
			rmdir($path);
		}
		if (is_dir($path = $this->getAbsoluteThumbDir_old())) {
			rmdir($path);
		}
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
     * @return ImageAlbum
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
     * Set categoryId
     *
     * @param integer $categoryId
     * @return ImageAlbum
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
     * Set dirName
     *
     * @param string $dirName
     * @return ImageAlbum
     */
    public function setDirName($dirName)
    {
        $this->dirName = $this->category->getDirName()."/".$dirName;
    
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
     * @return ImageAlbum
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
     * Set category
     *
     * @param \Site\GalleryBundle\Entity\ImageCategory $category
     * @return ImageAlbum
     */
    public function setCategory(\Site\GalleryBundle\Entity\ImageCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \Site\GalleryBundle\Entity\ImageCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add images
     *
     * @param \Site\GalleryBundle\Entity\Image $images
     * @return ImageAlbum
     */
    public function addImage(\Site\GalleryBundle\Entity\Image $images)
    {
        $this->images[] = $images;
    
        return $this;
    }

    /**
     * Remove images
     *
     * @param \Site\GalleryBundle\Entity\Image $images
     */
    public function removeImage(\Site\GalleryBundle\Entity\Image $images)
    {
        $this->images->removeElement($images);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set dictionary
     *
     * @param \Site\SiteBundle\Entity\DictionaryItem $dictionary
     * @return ImageAlbum
     */
    public function setDictionary(\Site\CoreBundle\Entity\DictionaryItem $dictionary = null)
    {
        $this->dictionary = $dictionary;
    
        return $this;
    }

    /**
     * Get dictionary
     *
     * @return \Site\CoreBundle\Entity\DictionaryItem 
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * Set allowAdd
     *
     * @param integer $allowAdd
     * @return ImageAlbum
     */
    public function setAllowAdd($allowAdd)
    {
        $this->allowAdd = $allowAdd;
    
        return $this;
    }

    /**
     * Get allowAdd
     *
     * @return integer 
     */
    public function getAllowAdd()
    {
        return $this->allowAdd;
    }

    /**
     * Set dictId
     *
     * @param integer $dictId
     * @return ImageAlbum
     */
    public function setDictId($dictId)
    {
        $this->dictId = $dictId;
    
        return $this;
    }

    /**
     * Get dictId
     *
     * @return integer 
     */
    public function getDictId()
    {
        return $this->dictId;
    }
}
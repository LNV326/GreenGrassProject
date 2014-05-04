<?php
namespace Site\GalleryBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Site\CoreBundle\Entity\UserConfigInfo as UserConfigInfo;

/**
 * @ORM\Entity(repositoryClass="Site\GalleryBundle\Repository\ImageRep")
 * @ORM\Table(name="s_gallery_images")
 * @ORM\HasLifecycleCallbacks
 */
class Image {
	const MAIN_UPLOAD_DIR = '../../images/www/gallery/';
	const THUMBNAILS_DIR = '/thumbs_x2/';
	const THUMBNAILS_DIR_OLD = '/thumbs/';
	const HOST_ADDRESS = 'http://images.nfsko.mooo.com/gallery/';
	
	const THUMBNAIL_WIDTH = '320';
	const THUMBNAIL_HEIGHT = '240';
	
	const THUMBNAIL_WIDTH_OLD = '150';
	const THUMBNAIL_HEIGHT_OLD = '112';	
	
	/**
	 * В случае, если одновременно загружается несколько изображений,
	 * а именно так и будет происходить в большинстве случаев,
	 * их регистрация происходит одновременно, поэтому имена будут уникальными.
	 * Данная переменная служит для внесения различий в названия подобных файлов
	 * и является порядковым номером изображения из группы загруженных
	 * @var unknown
	 * @deprecated
	 */
	protected $count = 0; 

	/**
	 * Файл изображения
	 */
	private $file;
	/**
	 * Файл миниатюры
	 */
	private $thumb;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(name="mid", type="integer", options={"unsigned"=true})
	 */
	protected $memberId;
	/**
	 * @ORM\Column(name="mname", type="string", length=32)
	 */
	protected $memberName;
	/**
	 * @ORM\Column(name="subcat", type="smallint", options={"unsigned"=true})
	 */
	protected $albumId;
	/**
	 * @ORM\Column(name="filename", type="string", length=32)
	 */
	protected $fileName;
	/**
	 * @ORM\Column(name="allow_add", type="boolean")
	 */
	protected $visibility;
	/**
	 * ORM\Column(name="status", type="string")
	 * Это ENUM поле, но в Doctrine такого нет =P
	 */
	protected $status;
	
	protected $width;
	
	protected $height;
	
	// ===== Связи =====
	
	/**
	 * @ORM\ManyToOne(targetEntity="ImageAlbum", inversedBy="images")
	 * @ORM\JoinColumn(name="subcat", referencedColumnName="id")
	 **/
	protected $album;
// 	/**
// 	 * @ORM\ManyToOne(targetEntity="Site\CoreBundle\Entity\UserConfigInfo")
// 	 * @ORM\JoinColumn(name="mid", referencedColumnName="id")
// 	 **/
// 	protected $member;	
	
	// ===== Важные функции =====
	
	public function __construct($count = 0) {
		$this->count = $count;
		$this->id = 0; 
		$this->file = null;
	}
	
	/**
	 * Sets file.
	 *
	 * @param UploadedFile $file
	 */
	public function setFile(UploadedFile $file = null) {
		$this->file = $file;
	}
	
	/**
	 * Get file.
	 *
	 * @return UploadedFile
	 */
	public function getFile() {
		return $this->file;
	}
	
	/**
	 * Возвращает абсолютный путь директории, в которой хранится изображение
	 * @return string
	 */
	protected function getUploadDir() {
		return self::MAIN_UPLOAD_DIR.$this->album->getDirName();
	}
	
	/**
	 * Возвращает абсолютный путь до изображения
	 * @return Ambigous <NULL, string>
	 */
	public function getAbsolutePath() {
		return $this->getUploadDir().'/'.$this->fileName;
	}
	
	/**
	 * Возвращает относительный путь до изображения
	 * @return Ambigous <NULL, string>
	 */
	public function getWebPath() {
		if ($this->album)
			return $this->album->getDirName().'/'.$this->fileName;
		else return null;
	}
	
	/**
	 * Возвращает абсолютный путь до миниатюры изображения
	 * @return Ambigous <NULL, string>
	 */
	public function getAbsoluteThumbPath() {
		return $this->getUploadDir().self::THUMBNAILS_DIR.$this->fileName;
	}
	
	// TODO костыль
	public function getAbsoluteThumbPath_old() {
		return $this->getUploadDir().self::THUMBNAILS_DIR_OLD.$this->fileName;
	}
	
	
	/**
	 * Возвращает относительный путь до миниатюры изображения
	 * @return Ambigous <NULL, string>
	 */
	public function getWebThumbPath() {
		if ($this->album)
			return $this->album->getDirName().self::THUMBNAILS_DIR.$this->fileName;
		else return null;
	}
		
	/**
	 * Создание миниатюры изображения
	 */
	private function createThumb($w, $h, $thumb) {
		$source = $this->getAbsolutePath();
			//Если есть большой и нет уменьшенного, то продолжаем
			if ((file_exists($source)) && (!file_exists($thumb))) {
				// определим коэффициент сжатия изображения, которое будем генерить
// 				$w = 150;
// 				$h = 112;
				$ratio = $w/$h;
				// получим размеры исходного изображения
				$size_img = getimagesize($source);
				// Если размеры меньше, то масштабирования не нужно
				if (($size_img[0]<$w) && ($size_img[1]<$h)) return true;
				// получим коэффициент сжатия исходного изображения
				$src_ratio=$size_img[0]/$size_img[1];
				// Здесь вычисляем размеры уменьшенной копии, чтобы при масштабировании сохранились пропорции исходного изображения
				if ($ratio<$src_ratio) {
					$h = $w/$src_ratio;
				} else{
					$w = $h*$src_ratio;
				}
				// создадим пустое изображение по заданным размерам
				$dest_img = imagecreatetruecolor($w, $h);
				$white = imagecolorallocate($dest_img, 255, 255, 255);
				if ($size_img[2]==2)  $src_img = imagecreatefromjpeg($source);
				else if ($size_img[2]==1) $src_img = imagecreatefromgif($source);
				else if ($size_img[2]==3) $src_img = imagecreatefrompng($source);
				// масштабируем изображение     функцией imagecopyresampled()
				// $dest_img - уменьшенная копия
				// $src_img - исходной изображение
				// $w - ширина уменьшенной копии
				// $h - высота уменьшенной копии
				// $size_img[0] - ширина исходного изображения
				// $size_img[1] - высота исходного изображения
				imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[0], $size_img[1]);
				// сохраняем уменьшенную копию в файл
				if ($size_img[2]==2)  imagejpeg($dest_img, $thumb);
				else if ($size_img[2]==1) imagegif($dest_img, $thumb);
				else if ($size_img[2]==3) imagepng($dest_img, $thumb);
				// чистим память от созданных изображений
				imagedestroy($dest_img);
				imagedestroy($src_img);
				return true;
			}
	}
	
	/**
	 * Создание различных миниатюр для изображения
	 */
	public function createThumbs() {
		// Создание миниатюры
		$thumb = $this->getAbsoluteThumbPath();
		$this->createThumb(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, $thumb);
		// TODO костыль
		// Создание миниатюры для старой галереи
		$thumb = $this->getAbsoluteThumbPath_old();
		$this->createThumb(self::THUMBNAIL_WIDTH_OLD, self::THUMBNAIL_HEIGHT_OLD, $thumb);
	}
	
	/**
	 * @ORM\PrePersist()
	 * Действия перед вставкой записи в базу данных
	 */
	public function createFileName() {
		if (null !== $this->getFile()) {
			// Создание имени файла как ID пользователя и метки времени
			$this->fileName = $this->memberId."_".(microtime(true)*10000).".".$this->getFile()->guessExtension();
		} else throw new \Exception('File is not set');
	}
	
	/**
	 * @ORM\PostPersist()
	 * Действия после вставки записи в базу данных
	 * Перемещать файл необходимо после того, как запись создана в БД, иначе на сервере будут лежать левые файлы, если запись не создастся.
	 */
	public function uploadFile() {
		if (null !== $this->getFile()) {
			// Перемещение и переименование файла
			$this->getFile()->move( $this->getUploadDir(), $this->fileName );
			// Создание миниатюр
			$this->createThumbs();
			$this->file = null;
		} else throw new \Exception('File is not set');
	}
	
	/**
	 * @ORM\PreRemove()
	 * Действия при удалении записи из базы данных
	 */
	public function removeFile() {
		if ( file_exists( $file = $this->getAbsolutePath() ) )
			unlink($file);
		if ( file_exists( $file = $this->getAbsoluteThumbPath() ) )
			unlink($file);
		if ( file_exists( $file = $this->getAbsoluteThumbPath_old() ) )
			unlink($file);
	}
	// ===== Автозаполнение =====



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
    	if ( file_exists($this->getAbsolutePath()) )
    		list($this->width, $this->height) = getimagesize( $this->getAbsolutePath() );
        return $this->id;
    }

    /**
     * Set memberId
     *
     * @param integer $memberId
     * @return Image
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;
    
        return $this;
    }

    /**
     * Get memberId
     *
     * @return integer 
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set memberName
     *
     * @param string $memberName
     * @return Image
     */
    public function setMemberName($memberName)
    {
        $this->memberName = $memberName;
    
        return $this;
    }

    /**
     * Get memberName
     *
     * @return string 
     */
    public function getMemberName()
    {
        return $this->memberName;
    }

    /**
     * Set albumId
     *
     * @param integer $albumId
     * @return Image
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
    
        return $this;
    }

    /**
     * Get albumId
     *
     * @return integer 
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return Image
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
   
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set visibility
     *
     * @param boolean $visibility
     * @return Image
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    
        return $this;
    }

    /**
     * Get visibility
     *
     * @return boolean 
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set album
     *
     * @param \Site\GalleryBundle\Entity\ImageAlbum $album
     * @return Image
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
    
    
    public function getWidth()
    {
        return $this->width;
    }
    
    public function getHeight()
    {
    	return $this->height;
    }
}
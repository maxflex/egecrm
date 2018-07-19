<?php

trait HasPhoto
{
	public function bindPhoto()
	{
		if (! $this->isNewRecord) {
			$this->has_photo_original = $this->hasPhotoOriginal();
			$this->photo_original_size = $this->photoOriginalSize();
			$this->photo_cropped_size = $this->photoCroppedSize();
			$this->photo_url = $this->photoUrl();
		}
	}

	public function photoPath($addon = '')
	{
		return static::UPLOAD_DIR . $this->id . $addon . '.' . $this->photo_extension;
	}

	public function photoUrl()
	{
		if ($this->hasPhotoCropped()) {
			$photo = $this->id . '.' . $this->photo_extension;
		} else {
			$photo = static::NO_PHOTO;
		}
		return static::UPLOAD_DIR . $photo;
	}

	public function hasPhotoOriginal()
	{
		return file_exists($this->photoPath('_original'));
	}

	public function hasPhotoCropped()
	{
		return file_exists($this->photoPath());
	}

	public function photoCroppedSize()
	{
		if ($this->hasPhotoCropped()) {
			return filesize($this->photoPath());
		} else {
			return 0;
		}
	}

	public function photoOriginalSize()
	{
		if ($this->hasPhotoOriginal()) {
			return filesize($this->photoPath('_original'));
		} else {
			return 0;
		}
	}
}

<?php
declare(strict_types=1);

namespace SGFilehoster;

use \Lazer\Classes\Database as DB;

class DataHandler
{
	/**
	 * Initializes database tables, in case they do not exist.
	 */
	public static function initDatabase()
	{
		// check if tables exist, if not, create them
		try {
			\Lazer\Classes\Helpers\Validate::table(\SGFilehoster\TABLE_UPLOAD)->exists();
		} catch (\Lazer\Classes\LazerException $e) {
			$tables = [
				\SGFilehoster\TABLE_UPLOAD => \SGFilehoster\TABLE_UPLOAD_STRUCTURE,
				\SGFilehoster\TABLE_FILE => \SGFilehoster\TABLE_FILE_STRUCTURE
			];

			// create tables
			foreach ($tables as $table => $structure) {
				DB::create($table, $structure);
			}

			// create upload->file relation
			\Lazer\Classes\Relation::table(\SGFilehoster\TABLE_UPLOAD)
				->hasMany(\SGFilehoster\TABLE_FILE)
				->localKey('search_id')
				->foreignKey('upload_id')
				->setRelation();
			
			// create file->upload relation
			\Lazer\Classes\Relation::table(\SGFilehoster\TABLE_FILE)
			->belongsTo(\SGFilehoster\TABLE_UPLOAD)
			->localKey('upload_id')
			->foreignKey('search_id')
			->setRelation();
		}
	}


	/**
	 * Creates a new upload entry in uploads table.
	 */
	public static function createUpload(array $data = [])
	{
		$upload = DB::table(\SGFilehoster\TABLE_UPLOAD);
		$upload->search_id = $data['search_id'] ?? -1;
		$upload->time_created = $data['time_created'] ?? time();
		$upload->time_destroyed = $data['time_destroyed'] ?? -1;
		$upload->password = $data['password'] ?? '';
		$upload->save();
	}


	/**
	 * Creates a new file entry in files table.
	 */
	public static function createFile(array $data = [])
	{
		$file = DB::table(\SGFilehoster\TABLE_FILE);
		$file->search_id = $data['search_id'] ?? -1;
		$file->upload_id = $data['upload_id'] ?? '';
		$file->file_name = $data['file_name'] ?? '';
		$file->original_name = $data['original_name'] ?? '';
		$file->size = $data['size'] ?? 0;
		$file->token = $data['token'] ?? \SGFilehoster\Utils::getRandomString();
		$file->save();
	}


	/**
	 * Returns upload for given searchId, or null if not found.
	 */
	public static function getUpload(string $searchId)
	{
		$upload = DB::table(\SGFilehoster\TABLE_UPLOAD)->where('search_id', '=', $searchId)->find();

		if (isset($upload->id)) {
			return $upload;
		}

		return null;
	}


	/**
	 * Returns file for given searchId, or null if not found.
	 */
	public static function getFile(string $searchId)
	{
		$file = DB::table(\SGFilehoster\TABLE_FILE)->where('search_id', '=', $searchId)->find();

		if (isset($file->id)) {
			return $file;
		}

		return null;
	}


	/**
	 * Checks if an upload with given searchId is available.
	 * Also checks if upload should be destroyed for given upload searchId.
	 * In case it must be destroyed, this method deletes the upload and returns false, else true.
	 */
	public static function uploadExists(string $searchId) : bool
	{
		$upload = self::getUpload($searchId);

		if ($upload !== null) {

			// check for destroy time
			if ($upload->time_destroyed !== -1 && $upload->time_destroyed < time()) {

				// destroy time is due, delete files and database entries
				self::deleteUpload($searchId);

				return false;
			}

			return true;
		}

		return false;
	}


	/**
	 * Checks if a file with given searchId is available.
	 */
	public static function fileExists(string $searchId) : bool
	{
		$file = self::getFile($searchId);

		if ($file !== null) {
			// check for destroy time of the related upload,
			// this will delete the upload and the file in case time is over.
			return self::uploadExists($file->upload_id);
		}

		return false;
	}


	/**
	 * Deletes database entry for given upload searchId.
	 * Also triggers deletion of related files.
	 */
	public static function deleteUpload(string $searchId)
	{
		$upload = DB::table(\SGFilehoster\TABLE_UPLOAD)->with(\SGFilehoster\TABLE_FILE)->where('search_id', '=', $searchId)->find();

		// delete individual files
		foreach ($upload->{\SGFilehoster\TABLE_FILE}->findAll() as $file) {
			self::deleteFile($file->search_id);
		}
		$upload->delete();
	}


	/**
	 * Deletes database entry for given file searchId.
	 * Also deletes related physical file.
	 */
	public static function deleteFile(string $searchId)
	{
		$file = DB::table(\SGFilehoster\TABLE_FILE)->where('search_id', '=', $searchId)->find();
		try {
			unlink(realpath(\SGFilehoster\UPLOAD_PATH . $file->file_name));
		} catch(\Exception $e) {
			throw new \SGFilehoster\SGException('File could not be deleted.');
		}
		$file->delete();
	}


	/**
	 * Returns files related to given upload searchId, or null if upload was not found.
	 */
	public static function getFilesForUpload(string $uploadId)
	{
		$upload = DB::table(\SGFilehoster\TABLE_UPLOAD)->with(\SGFilehoster\TABLE_FILE)->where('search_id', '=', $uploadId)->find();

		if (isset($upload->id)) {
			return $upload->{\SGFilehoster\TABLE_FILE}->findAll();
		}

		return null;
	}


	/**
	 * Returns file with related upload for given file searchId, or null if file was not found.
	 */
	public static function getFileWithUpload(string $fileId)
	{
		$file = DB::table(\SGFilehoster\TABLE_FILE)->with(\SGFilehoster\TABLE_UPLOAD)->where('search_id', '=', $fileId)->find();

		if (isset($file->id)) {
			return $file;
		}

		return null;
	}


	/**
	 * Returns all available uploads with related files.
	 */
	public static function getAllUploadsWithFiles() : DB
	{
		return DB::table(\SGFilehoster\TABLE_UPLOAD)->with(\SGFilehoster\TABLE_FILE)->orderBy('time_created', 'DESC')->findAll();
	}


	/**
	 * Serves the related file for given searchId to the user.
	 */
	public static function serveFile(string $searchId)
	{
		$file = self::getFile($searchId);
		if ($file !== null) {
			$path = realpath(\SGFilehoster\UPLOAD_PATH . $file->file_name);
			$finfo_mime = finfo_open(FILEINFO_MIME_TYPE);
			$finfo_encoding = finfo_open(FILEINFO_MIME_ENCODING);
			ignore_user_abort(true);
			set_time_limit(0);
			header('Content-Type: ' . finfo_file($finfo_mime, $path));
			header('Content-Transfer-Encoding: ' . finfo_file($finfo_encoding, $path));
			header('Content-Disposition: attachment; filename="' . $file->original_name . '"');
			header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
			header('Expires: 0');
			flush();
			readfile($path);
		}
	}
}

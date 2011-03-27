<?php
/**
* Podcast Manager for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
*/

/**
 * @package		Podcast Manager
 * @subpackage	com_podcastmedia
 */
abstract class PodcastMediaHelper
{
	/**
	 * Gets the icon type
	 * 
	 * @param string The filename
	 * @return boolean
	 */
	public static function getTypeIcon($fileName)
	{
		// Get file extension
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param array File information
	 * @param string An error message to be returned
	 * @return boolean
	 */
	public static function canUpload($file, &$err)
	{
		$medmanparams	= JComponentHelper::getParams('com_media');
		$podmanparams	= JComponentHelper::getParams('com_podcastmanager');
		$podmedparams	= JComponentHelper::getParams('com_podcastmedia');
		
		if (empty($file['name'])) {
			$err = 'COM_PODCASTMEDIA_ERROR_UPLOAD_INPUT';
			return false;
		}

		jimport('joomla.filesystem.file');
		if ($file['name'] !== JFile::makesafe($file['name'])) {
			$err = 'COM_PODCASTMEDIA_ERROR_WARNFILENAME';
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		$allowable = explode(',', 'mp3,m4a,mov,mp4,m4v');
		$ignored = explode(',', $medmanparams->get('ignore_extensions'));
		if (!in_array($format, $allowable) && !in_array($format,$ignored))
		{
			$err = 'COM_PODCASTMEDIA_ERROR_WARNFILETYPE';
			return false;
		}

		$maxSize = (int) ($medmanparams->get('upload_maxsize', 0) * 1024 * 1024);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'COM_PODCASTMEDIA_ERROR_WARNFILETOOLARGE';
			return false;
		}

		$user = JFactory::getUser();
		$imginfo = null;
		if ($medmanparams->get('restrict_uploads',1)) {
			if (!in_array($format, $ignored)) {
				// if its not an image...and we're not ignoring it
				$allowed_mime = explode(',', 'audio/mpeg,audio/x-m4a,video/mp4,video/x-m4v,video/quicktime');
				$illegal_mime = explode(',', $medmanparams->get('upload_mime_illegal'));
				if (function_exists('finfo_open') && $medmanparams->get('check_mime',1)) {
					// We have fileinfo
					$finfo = finfo_open(FILEINFO_MIME);
					$type = finfo_file($finfo, $file['tmp_name']);
					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
						$err = 'COM_PODCASTMEDIA_ERROR_WARNINVALID_MIME';
						return false;
					}
					finfo_close($finfo);
				} else if (function_exists('mime_content_type') && $medmanparams->get('check_mime',1)) {
					// we have mime magic
					$type = mime_content_type($file['tmp_name']);
					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
						$err = 'COM_PODCASTMEDIA_ERROR_WARNINVALID_MIME';
						return false;
					}
				} else if (!$user->authorise('core.manage')) {
					$err = 'COM_PODCASTMEDIA_ERROR_WARNNOTADMIN';
					return false;
				}
			}
		}

		$xss_check =  JFile::read($file['tmp_name'],false,256);
		$html_tags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				$err = 'COM_PODCASTMEDIA_ERROR_WARNIEXSS';
				return false;
			}
		}
		return true;
	}

	public static function parseSize($size)
	{
		if ($size < 1024) {
			return JText::sprintf('COM_PODCASTMEDIA_FILESIZE_BYTES', $size);
		}
		elseif ($size < 1024 * 1024) {
			return JText::sprintf('COM_PODCASTMEDIA_FILESIZE_KILOBYTES', sprintf('%01.2f', $size / 1024.0));
		}
		else {
			return JText::sprintf('COM_PODCASTMEDIA_FILESIZE_MEGABYTES', sprintf('%01.2f', $size / (1024.0 * 1024)));
		}
	}

	public static function countFiles($dir)
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir)) {
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if (substr($entry, 0, 1) != '.' && is_file($dir.DIRECTORY_SEPARATOR.$entry) && strpos($entry, '.html') === false && strpos($entry, '.php') === false) {
					$total_file++;
				}
				if (substr($entry, 0, 1) != '.' && is_dir($dir.DIRECTORY_SEPARATOR.$entry)) {
					$total_dir++;
				}
			}

			$d->close();
		}
		return array ($total_file, $total_dir);
	}
}

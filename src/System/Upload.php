<?php
namespace enflares\System;

/**
 * Class Upload
 * @package enflares\System
 */
class Upload
{
    static $mime_map = array(
        'video/3gpp2'                                                               => '3g2',
        'video/3gp'                                                                 => '3gp',
        'video/3gpp'                                                                => '3gp',
        'application/x-compressed'                                                  => '7zip',
        'audio/x-acc'                                                               => 'aac',
        'audio/ac3'                                                                 => 'ac3',
        'application/postscript'                                                    => 'ai',
        'audio/x-aiff'                                                              => 'aif',
        'audio/aiff'                                                                => 'aif',
        'audio/x-au'                                                                => 'au',
        'video/x-msvideo'                                                           => 'avi',
        'video/msvideo'                                                             => 'avi',
        'video/avi'                                                                 => 'avi',
        'application/x-troff-msvideo'                                               => 'avi',
        'application/macbinary'                                                     => 'bin',
        'application/mac-binary'                                                    => 'bin',
        'application/x-binary'                                                      => 'bin',
        'application/x-macbinary'                                                   => 'bin',
        'image/bmp'                                                                 => 'bmp',
        'image/x-bmp'                                                               => 'bmp',
        'image/x-bitmap'                                                            => 'bmp',
        'image/x-xbitmap'                                                           => 'bmp',
        'image/x-win-bitmap'                                                        => 'bmp',
        'image/x-windows-bmp'                                                       => 'bmp',
        'image/ms-bmp'                                                              => 'bmp',
        'image/x-ms-bmp'                                                            => 'bmp',
        'application/bmp'                                                           => 'bmp',
        'application/x-bmp'                                                         => 'bmp',
        'application/x-win-bitmap'                                                  => 'bmp',
        'application/cdr'                                                           => 'cdr',
        'application/coreldraw'                                                     => 'cdr',
        'application/x-cdr'                                                         => 'cdr',
        'application/x-coreldraw'                                                   => 'cdr',
        'image/cdr'                                                                 => 'cdr',
        'image/x-cdr'                                                               => 'cdr',
        'zz-application/zz-winassoc-cdr'                                            => 'cdr',
        'application/mac-compactpro'                                                => 'cpt',
        'application/pkix-crl'                                                      => 'crl',
        'application/pkcs-crl'                                                      => 'crl',
        'application/x-x509-ca-cert'                                                => 'crt',
        'application/pkix-cert'                                                     => 'crt',
        'text/css'                                                                  => 'css',
        'text/x-comma-separated-values'                                             => 'csv',
        'text/comma-separated-values'                                               => 'csv',
        'application/vnd.msexcel'                                                   => 'csv',
        'application/x-director'                                                    => 'dcr',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/x-dvi'                                                         => 'dvi',
        'message/rfc822'                                                            => 'eml',
        'application/x-msdownload'                                                  => 'exe',
        'video/x-f4v'                                                               => 'f4v',
        'audio/x-flac'                                                              => 'flac',
        'video/x-flv'                                                               => 'flv',
        'image/gif'                                                                 => 'gif',
        'application/gpg-keys'                                                      => 'gpg',
        'application/x-gtar'                                                        => 'gtar',
        'application/x-gzip'                                                        => 'gzip',
        'application/mac-binhex40'                                                  => 'hqx',
        'application/mac-binhex'                                                    => 'hqx',
        'application/x-binhex40'                                                    => 'hqx',
        'application/x-mac-binhex40'                                                => 'hqx',
        'text/html'                                                                 => 'html',
        'image/x-icon'                                                              => 'ico',
        'image/x-ico'                                                               => 'ico',
        'image/vnd.microsoft.icon'                                                  => 'ico',
        'text/calendar'                                                             => 'ics',
        'application/java-archive'                                                  => 'jar',
        'application/x-java-application'                                            => 'jar',
        'application/x-jar'                                                         => 'jar',
        'image/jp2'                                                                 => 'jp2',
        'video/mj2'                                                                 => 'jp2',
        'image/jpx'                                                                 => 'jp2',
        'image/jpm'                                                                 => 'jp2',
        'image/jpeg'                                                                => 'jpeg',
        'image/pjpeg'                                                               => 'jpeg',
        'application/x-javascript'                                                  => 'js',
        'application/json'                                                          => 'json',
        'text/json'                                                                 => 'json',
        'application/vnd.google-earth.kml+xml'                                      => 'kml',
        'application/vnd.google-earth.kmz'                                          => 'kmz',
        'text/x-log'                                                                => 'log',
        'audio/x-m4a'                                                               => 'm4a',
        'application/vnd.mpegurl'                                                   => 'm4u',
        'audio/midi'                                                                => 'mid',
        'application/vnd.mif'                                                       => 'mif',
        'video/quicktime'                                                           => 'mov',
        'video/x-sgi-movie'                                                         => 'movie',
        'audio/mpeg'                                                                => 'mp3',
        'audio/mpg'                                                                 => 'mp3',
        'audio/mpeg3'                                                               => 'mp3',
        'audio/mp3'                                                                 => 'mp3',
        'video/mp4'                                                                 => 'mp4',
        'video/mpeg'                                                                => 'mpeg',
        'application/oda'                                                           => 'oda',
        'audio/ogg'                                                                 => 'ogg',
        'video/ogg'                                                                 => 'ogg',
        'application/ogg'                                                           => 'ogg',
        'application/x-pkcs10'                                                      => 'p10',
        'application/pkcs10'                                                        => 'p10',
        'application/x-pkcs12'                                                      => 'p12',
        'application/x-pkcs7-signature'                                             => 'p7a',
        'application/pkcs7-mime'                                                    => 'p7c',
        'application/x-pkcs7-mime'                                                  => 'p7c',
        'application/x-pkcs7-certreqresp'                                           => 'p7r',
        'application/pkcs7-signature'                                               => 'p7s',
        'application/pdf'                                                           => 'pdf',
        'application/octet-stream'                                                  => 'pdf',
        'application/x-x509-user-cert'                                              => 'pem',
        'application/x-pem-file'                                                    => 'pem',
        'application/pgp'                                                           => 'pgp',
        'application/x-httpd-php'                                                   => 'php',
        'application/php'                                                           => 'php',
        'application/x-php'                                                         => 'php',
        'text/php'                                                                  => 'php',
        'text/x-php'                                                                => 'php',
        'application/x-httpd-php-source'                                            => 'php',
        'image/png'                                                                 => 'png',
        'image/x-png'                                                               => 'png',
        'application/powerpoint'                                                    => 'ppt',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.ms-office'                                                 => 'ppt',
        'application/msword'                                                        => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/x-photoshop'                                                   => 'psd',
        'image/vnd.adobe.photoshop'                                                 => 'psd',
        'audio/x-realaudio'                                                         => 'ra',
        'audio/x-pn-realaudio'                                                      => 'ram',
        'application/x-rar'                                                         => 'rar',
        'application/rar'                                                           => 'rar',
        'application/x-rar-compressed'                                              => 'rar',
        'audio/x-pn-realaudio-plugin'                                               => 'rpm',
        'application/x-pkcs7'                                                       => 'rsa',
        'text/rtf'                                                                  => 'rtf',
        'text/richtext'                                                             => 'rtx',
        'video/vnd.rn-realvideo'                                                    => 'rv',
        'application/x-stuffit'                                                     => 'sit',
        'application/smil'                                                          => 'smil',
        'text/srt'                                                                  => 'srt',
        'image/svg+xml'                                                             => 'svg',
        'application/x-shockwave-flash'                                             => 'swf',
        'application/x-tar'                                                         => 'tar',
        'application/x-gzip-compressed'                                             => 'tgz',
        'image/tiff'                                                                => 'tiff',
        'text/plain'                                                                => 'txt',
        'text/x-vcard'                                                              => 'vcf',
        'application/videolan'                                                      => 'vlc',
        'text/vtt'                                                                  => 'vtt',
        'audio/x-wav'                                                               => 'wav',
        'audio/wave'                                                                => 'wav',
        'audio/wav'                                                                 => 'wav',
        'application/wbxml'                                                         => 'wbxml',
        'video/webm'                                                                => 'webm',
        'audio/x-ms-wma'                                                            => 'wma',
        'application/wmlc'                                                          => 'wmlc',
        'video/x-ms-wmv'                                                            => 'wmv',
        'video/x-ms-asf'                                                            => 'wmv',
        'application/xhtml+xml'                                                     => 'xhtml',
        'application/excel'                                                         => 'xl',
        'application/msexcel'                                                       => 'xls',
        'application/x-msexcel'                                                     => 'xls',
        'application/x-ms-excel'                                                    => 'xls',
        'application/x-excel'                                                       => 'xls',
        'application/x-dos_ms_excel'                                                => 'xls',
        'application/xls'                                                           => 'xls',
        'application/x-xls'                                                         => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-excel'                                                  => 'xlsx',
        'application/xml'                                                           => 'xml',
        'text/xml'                                                                  => 'xml',
        'text/xsl'                                                                  => 'xsl',
        'application/xspf+xml'                                                      => 'xspf',
        'application/x-compress'                                                    => 'z',
        'application/x-zip'                                                         => 'zip',
        'application/zip'                                                           => 'zip',
        'application/x-zip-compressed'                                              => 'zip',
        'application/s-compressed'                                                  => 'zip',
        'multipart/x-zip'                                                           => 'zip',
        'text/x-scriptzsh'                                                          => 'zsh',
    );

    /**
     * Convert mime type to file extension
     * @param $mime
     * @return mixed
     */
    public static function ExtensionFromMimeType($mime){
        if(isset(self::$mime_map[$mime])) {
            return self::$mime_map[$mime];
        }
    }

    /**
     * Convert file extension to mime type
     * @param $ext
     * @return int|string
     */
    public static function ExtensionToMimeType($ext){
        foreach( self::$mime_map as $mime=>$type )
            if( !strcasecmp($ext, $type) ) return $mime;
    }

    protected $field;
    protected $name;
    protected $file;
    protected $size;
    protected $type;
    protected $index;
    protected $error;

    private $width;
    private $height;

    public function __construct($field, $index=NULL)
    {
        $this->field = $field;
        $this->index = $index;

        if( is_null($index) )
        {
            if( isset($_FILES[$field]['tmp_name']) )
            {
                $this->name = $_FILES[$field]['name'];
                $this->type = $_FILES[$field]['type'];
                $this->error = $_FILES[$field]['error'];
                $this->file = $_FILES[$field]['tmp_name'];
                $this->size = $_FILES[$field]['size'];
            }
        }else{
            if( isset($_FILES[$field]['tmp_name'][$index]) )
            {
                $this->name = $_FILES[$field]['name'][$index];
                $this->type = $_FILES[$field]['type'][$index];
                $this->error = $_FILES[$field]['error'][$index];
                $this->file = $_FILES[$field]['tmp_name'][$index];
                $this->size = $_FILES[$field]['size'][$index];
            }
        }
    }

    public function __get($key)
    {
        if( method_exists($this, $func='get'.$key) )
        {
            return $this->$func();
        }
        elseif( property_exists($this, $key) )
        {
            return $this->$key;
        }
    }

    public function __toString()
    {
        return "$this->file";
    }

    /**
     * Return a unique key of the uploaded data
     * @return false|string
     */
    public function getKey(){
        if( $file = realpath($this->file) )
        {
            return md5_file($file);
        }
    }

    /**
     * Return a unique file name of the uploaded data
     * @return string
     */
    public function getKeyName(){
        $ext = $this->getExt();
        if( $key = $this->getKey() ){
            return "$key.$ext";
        }
    }

    /**
     * Return the extension name of the uploaded file
     * @return string
     */
    public function getExt(){
        $ext = $this->name ? trim( pathinfo($this->name, PATHINFO_EXTENSION), '.') : NULL;
        return $ext ?: static::ExtensionFromMimeType($this->getMimeType());
    }

    /**
     * To check the mime-type of the uploaded file
     * @return string
     */
    public function getMimeType()
    {
        if( !$this->type || !$this->width || !$this->height )
        {
            if( ($file = realpath($this->file)) && is_file($file) )
            {
                $info = getimagesize($file);
                if( isset($info['mime']) )
                {
                    $this->type = $info['mime'];
                }

                if( isset($info[0]) )
                {
                    $this->width = $info[0];
                }

                if( isset($info[1]) )
                {
                    $this->height = $info[1];
                }
            }
        }

        return $this->type;
    }

    /**
     * To check the width of the uploaded image
     * @return int|null
     */
    public function getWidth()
    {
        if( !$this->width ) $this->getMimeType();
        return $this->width;
    }

    /**
     * To check the height of the uploaded image
     * @return int|null
     */
    public function getHeight()
    {
        if( !$this->height ) $this->getMimeType();
        return $this->height;
    }

    public function isAccepted()
    {
        return !$this->isDenied();
    }

    public function isDenied()
    {
        switch( strtolower($this->getExt()) )
        {
            case 'php': case 'php3': case 'php4': case 'php5': case 'php7':
                return TRUE;
            break;
        }
    }

    /**
     * To check if the file is successfully uploaded
     * @param null $maxSize The maximum size of the file
     * @param null $accepts The acceptable extensions of a file
     * @param null $denies  The unacceptable extensions of a file
     * @return bool
     */
    public function isUploaded($maxSize=NULL, $accepts=NULL, $denies=NULL){

        if( !$this->error && is_uploaded_file($this->file) )
        {
            $maxSize = intval($maxSize ?: env('UPLOAD_MAX_SIZE'));

            if( $maxSize && ($this->size>$maxSize) )
            {
                $this->error = UPLOAD_ERR_INI_SIZE;
                return FALSE;
            }
            
            $ext = $this->getExt();
            foreach( preg_split('/[,;]\s*/', $denies ?: env('UPLOAD_DENIES')) as $deny )
            {
                if( !strcasecmp($ext, $deny) )
                {
                    $this->error = UPLOAD_ERR_EXTENSION;
                    return FALSE;
                }
            }

            foreach( preg_split('/[,;]\s*/', $accepts = $accepts ?: env('UPLOAD_ACCEPTS')) as $accept )
                if( !strcasecmp($ext, $accept) ) return TRUE;

            if( empty($accepts) ) return TRUE;

            $this->error = UPLOAD_ERR_EXTENSION;
        }
        return FALSE;
    }

    /**
     * Return the latest error from upload process
     * @return string|null
     */
    public function getMessage()
    {
        switch( $this->error )
        {
            case 0:
            case '':
                return NULL;

            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file is too large';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'The uploaded file is unsaved';
            case UPLOAD_ERR_CANT_WRITE:
                return 'The uploaded file is unable to write';
            case UPLOAD_ERR_EXTENSION:
                return 'Unsupported file uploaded';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file is incomplete';
            default:
                return 'Unknown error';
        }
    }

    /**
     * To check if the uploaded file is an image, maybe within a given size
     * @param int|null $width   The maximum width of the image
     * @param int|null $height  The maximum height of the image
     * @return bool
     */
    public function isImage($width=NULL, $height=NULL)
    {
        if( preg_match('/image\/\w+/i', $this->getMimeType()) )
        {
            $flag = TRUE;

            if( $width=intval($width) )
            {
                $flag = $flag && ($this->getWidth()<=$width);
            }

            if( $height=intval($height) )
            {
                $flag = $flag && ($this->getHeight()<=$height);
            }

            return $flag;
        }
    }

    /**
     * To check if the uploaded file is an audio
     * @return bool
     */
    public function isAudio()
    {
        return !!preg_match('/audio\/\w+/i', $this->getMimeType());
    }

    /**
     * To check if the uploaded file is a video
     * @return bool
     */
    public function isVideo()
    {
        return !!preg_match('/video\/\w+/i', $this->getMimeType());
    }

    /**
     * Save the uploaded file to a path with a safe name
     * @param string $path
     * @param bool $overwrite
     * @return bool
     */
    public function saveTo($path, $overwrite=NULL){
        $name = $this->getKeyName() ?: (md5(microtime().$path) . '.' . $this->getExt()) ;
        $file = $path . '/' . $name;

        return $this->saveAs($file, $overwrite);
    }

    /**
     * Save the upload file to a specific location with a given name
     * @param string $file
     * @param bool $overwrite
     * @return bool
     */
    public function saveAs($file, $overwrite=NULL){
        $path = dirname($file);

        if( !is_dir($path) )
        {
            if( !mkdir($path, 0777, TRUE) )
            {
                $this->error = UPLOAD_ERR_CANT_WRITE;
                return FALSE;
            }
        }

        if( realpath($file) )
        {
            if( $overwrite )
            {
                if( !unlink($file) )
                {
                    $this->error = UPLOAD_ERR_CANT_WRITE;
                    return FALSE;
                }
            }else{
                $this->error = UPLOAD_ERR_CANT_WRITE;
                return FALSE;
            }
        }

        $origin = $this->file;
        if( is_uploaded_file($origin) ){
            return move_uploaded_file($origin, $file);
        }else {
            return rename($file, $this->file);
        }
    }

    /**
     * Open the uploaded file as image
     * @return resource
     */
    public function image()
    {
        if( realpath($this->file) )
        {
            return imagecreatefromstring(file_get_contents($this->file));
        }
    }

    /**
     * Open the uploaded file as text
     * @return string
     */
    public function text()
    {
        if( realpath($this->file) )
        {
            return file_get_contents($this->file);
        }
    }

    /**
     * Open the uploaded file as a json array
     * @return array
     */
    public function json()
    {
        if( realpath($this->file) )
        {
            return json_decode(file_get_contents($this->file), TRUE);
        }
    }

    /**
     * Open the uploaded file as csv format
     * @param bool $firstLineIsHeader
     * @param int $skipLinesAfterHeaders
     * @param int $skipLinesBeforeHeaders
     * @return array
     */
    public function csv($firstLineIsHeader=NULL, $skipLinesAfterHeaders=NULL, $skipLinesBeforeHeaders=NULL){
        if( $file=realpath($this->file) ){
            if( $fp=fopen($file, 'r') ){
                // Skip lines before headers
                for($i=intval($skipLinesBeforeHeaders); $i>0; $i--) fgetcsv($fp);

                // Read the header rows
                if( $firstLineIsHeader )
                {
                    $headers = fgetcsv($fp);
                    $count = count($headers);
                }
                else
                {
                    $count = 0;
                }

                // Skip lines after headers
                for($i=intval($skipLinesAfterHeaders); $i>0; $i--) fgetcsv($fp);

                // Read the data rows
                $result = array();
                while( $rs=fgetcsv($fp) ){
                    if( $count ){
                        for($i=count($rs); $i<$count; $i++){
                            $rs[] = NULL;
                        }
                        $rs = array_combine($headers, array_slice($rs, 0, $count));
                    }
                    $result[] = $rs;
                }

                fclose($fp);

                return $result;
            }
        }
    }

    /**
     * Create a thumbnail from the uploaded file by zooming the image to a given size with a ratio.
     * @param int $width
     * @param null $height
     * @return false|resource
     */
    public function thumb($width, $height=NULL)
    {
        if( $img = $this->image() ){
            $width = intval($width);
            $height = intval($height);

            if( !$width && !$height ) return $img;

            $a = $w = imagesx($img);
            $b = $h = imagesy($img);

            if( $width>$w ){
                $a = $width;
                $b = $height * $w/$width;
            }
            if( $height>$h ){
                $b = $height;
                $a = $a * $h/$height;
            }

            $thumb = imagecreatetruecolor($a, $b);
            imagecopyresampled($thumb, $img, 0, 0, 0, 0, $a, $b, $w, $h);
            imagedestroy($img);

            return $thumb;
        }
    }
}
<?php

class FileWriteComponent extends Component
{
    /**
     * var name of component "FileWrite"
     */
    var $name = "FileWrite";

    /**
     * var string
     */
    var $file_write_path = "files/";

    /**
     * <p> function take @param $file and create directory then
     *  write file to directed directory and return message
     * </p>
     * @param  $file array
     * <p> array contain information about the file uploaded </p>
     * @return string $message
     */
    function _write_file($file,$img_name)
    {

        $tmpName = $file['tmp_name'];
        if (is_uploaded_file($tmpName)) {
            $handle = fopen($tmpName, "r");
            $size = $file['size'];
            //$name = $file['name'];
            $name =$img_name;
            $fileData = fread($handle, $size);
            $path = WWW_ROOT . $this->file_write_path;
			
            $this->file_write_path=$path;
            //$this->_create_directory();
            $fileName = $this->file_write_path . $name;
            $handle2 = fopen($fileName, "a");
            $msg = fwrite($handle2, $fileData);
            if ($msg > 0) {
                $message = null;
            } else {
                $message = "File Is corrupt";
            }
        }
        else {
            $message = "File Is Not Writable";
        }
        return $message;
    }

    // <p> function "_write_file" end here </p>

    /**
     * <p>function take @param $file_path and
     * read file from provided file path then create directory then
     * write file to directed directory and return message
     * </p>
     * @param  $file_path string
     * <p> string contain path of file</p>
     * @return string $message
     */
    function write_edit_file($file_path)
    {
        $file = split("/", $file_path);
        $name = $file[count($file) - 1];
        $fileName = $this->file_write_path . $name;
        if ($fileName != $file_path) {
            $this->_create_directory();
            $handle = fopen($file_path, "r");
            $size = filesize($file_path);
            $fileData = fread($handle, $size);
            $handle2 = fopen($fileName, "a");
            $msg = fwrite($handle2, $fileData);
            if ($msg > 0) {
                $message = null;
                fflush($handle2);
                fclose($handle2);
                fclose($handle);
                $this->delete_file($file_path);
            } else {
                $message = "File is currpted";
            }
            return $message;
        }
    }

    //<p> function "write_edit_file" end here</p>

    /**
     * <p> function take @params $file_size and $defined_size
     * and check file size and return message if error else null </p>
     * @param  $file_size int
     * <p> int contain size of file </p>
     * @param  $defined_size int
     * <p> int contain defined size of the file </p>
     * @return string message or mull
     */
    function check_file_size($file_size, $defined_size)
    {
        if ($defined_size < $file_size) {
            return "Selected file size is more than defined file size";
        }
        return null;
    }

    //<p> function "check_file_size" end here </p>

    /**
     * <p> function create directory according to the
     *  file_write_path provided </p>
     * @return void
     */

    function _create_directory()
    {
        try {
            if (!is_dir($this->file_write_path)) {
                mkdir($this->file_write_path, 0777, true);
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    //<p> function "_create_directory"end here </p>

    /**
     * <p>delete file from folder by giving path </p>
     * @param  $file_path path of the file.
     * @return void
     */
    function delete_file($file_path)
    {
        $file = new File(realpath($file_path));
        if ($file->exists()) {
            $file->delete();
        }
    }

    //<p> function "delete_file" end here </p>


}


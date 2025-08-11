<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Session_files_driver extends CI_Session_driver implements SessionHandlerInterface {

    protected $_save_path;
    protected $_file_handle;
    protected $_file_path;
    protected $_file_new;
    protected $_session_id;

    #[\ReturnTypeWillChange]
    public function open($save_path, $name) {
        if (!is_dir($save_path)) {
            if (!mkdir($save_path, 0700, TRUE)) {
                log_message('error', "Session: Configured save path '".$save_path."' is not a directory, doesn't exist or cannot be created.");
                return FALSE;
            }
        } elseif (!is_writable($save_path)) {
            log_message('error', "Session: Configured save path '".$save_path."' is not writable by the PHP process.");
            return FALSE;
        }

        $this->_save_path = $save_path;
        return TRUE;
    }

    #[\ReturnTypeWillChange]
    public function close() {
        if (is_resource($this->_file_handle)) {
            flock($this->_file_handle, LOCK_UN);
            fclose($this->_file_handle);
            $this->_file_handle = NULL;
        }
        return TRUE;
    }

    #[\ReturnTypeWillChange]
    public function read($session_id) {
        $this->_file_path = $this->_save_path . DIRECTORY_SEPARATOR . 'ci_session_' . $session_id;

        if (!$this->_file_handle = @fopen($this->_file_path, 'c+b')) {
            log_message('error', "Session: Unable to open file '" . $this->_file_path . "'.");
            return '';
        }

        flock($this->_file_handle, LOCK_EX);
        $this->_file_new = filemtime($this->_file_path) === FALSE;

        $session_data = '';
        while (!feof($this->_file_handle)) {
            $session_data .= fread($this->_file_handle, 1024);
        }

        return $session_data;
    }

    #[\ReturnTypeWillChange]
    public function write($session_id, $session_data) {
        if ($session_id !== $this->_session_id && $this->_file_handle !== NULL) {
            $this->close();
            $this->_file_path = $this->_save_path . DIRECTORY_SEPARATOR . 'ci_session_' . $session_id;
            $this->_file_handle = fopen($this->_file_path, 'c+b');
        }

        if (!$this->_file_handle) {
            return FALSE;
        }

        $this->_session_id = $session_id;

        rewind($this->_file_handle);
        $written = fwrite($this->_file_handle, $session_data);
        fflush($this->_file_handle);

        return is_int($written);
    }

    #[\ReturnTypeWillChange]
    public function destroy($session_id) {
        if ($this->close()) {
            return @unlink($this->_save_path . DIRECTORY_SEPARATOR . 'ci_session_' . $session_id);
        }
        return FALSE;
    }

    #[\ReturnTypeWillChange]
    public function gc($maxlifetime) {
        foreach (glob($this->_save_path . DIRECTORY_SEPARATOR . 'ci_session_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && is_file($file)) {
                @unlink($file);
            }
        }

        return TRUE;
    }
}

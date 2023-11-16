<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validation_lib
{
    public function respondSuccess($data = null, $status_code = 200, $metadata = null)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status_code);
        if (!is_null($data))
            $response['data'] = $data;
        else
            $response['data'] = null;

        $response['success'] = true;
        if (!is_null($metadata))
            $response['metadata'] = $metadata;

        echo json_encode($response);
        die();
    }

    public function respondError($message, $template = 'error_general', $status_code = 500)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status_code);
        $response = [
            'error' => [
                'domain' => 'Exception',
                'message' => $message
            ],
            'success' => false
        ];
        echo json_encode($response);
        die();

    }

    public function respondNotFound($message, $template = 'error_general', $status_code = 404)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status_code);
        $response = [
            'error' => [
                'domain' => 'Exception',
                'message' => $message
            ],
            'success' => false
        ];
        echo json_encode($response);
        die();

    }

    public function validateFile($base64EncodeFile, $types = array(), $size = null, $field = null)
    {
        $image_file = base64_decode($base64EncodeFile);
        $f = finfo_open();
        $mime = finfo_buffer($f, $image_file, FILEINFO_MIME_TYPE);

        $mimes =& get_mimes();
        if (!empty($types)) {
            $status = false;
            foreach ($types as $type) {
                if (is_array($mimes[$type])) {
                    if (in_array($mime, $mimes[$type], true)) {
                        $status = true;
                        break;
                    }
                } else if ($mime === $mimes[$type]) {
                    $status = true;
                    break;
                }
            }
            if (!$status) {
                if ($field) {
                    $this->respondError([$field => 'Tipe file yang diijinkan:' . join(',', $types)]);
                } else {
                    $this->respondError('Tipe file yang diijinkan:' . join(',', $types));
                }
            }
        }
        if ($size) {
            $maxSize = $size / 1000000;
            $size_in_bytes = (int)(strlen(rtrim($base64EncodeFile, '=')) * 3 / 4);
            if ($size < $size_in_bytes) {
                if ($field) {
                    $this->respondError([$field => 'Ukuran file yang diijinkan:' . $maxSize . 'MB']);
                } else {
                    $this->respondError('Ukuran file yang diijinkan:' . $maxSize . 'MB');
                }

            }
        }
        return true;
    }


    public function get_extension($image_file)
    {
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $image_file, FILEINFO_MIME_TYPE);
        $mimes =& get_mimes();
        foreach ($mimes as $key => $mime) {
            if (is_array($mime)) {
                if (in_array($mime_type, $mime, true)) {
                    return '.' . $key;
                }
            } else if ($mime === $mime_type) {
                return '.' . $key;
            }
        }
        return '';
    }

    public function convertFile($path, $fileName)
    {
      // dd($fileName);
        if ($fileName != null) {
            if (!file_exists(FCPATH . $path . $fileName)) {
                return null;
            }
            $file = base_url($path . $fileName . '?v='.time());
            return $file;
        }
        return null;
    }

    public function convertImage($path, $fileName)
    {
        if ($fileName != null) {
            if (!file_exists(FCPATH . $path . $fileName)) {
                return null;
            }
            $path = base_url($path . $fileName);
            $img = file_get_contents($path);
            $data['extension'] = $this->get_image_extension($img);
            $data['data'] = base64_encode($img);

            return $data;
        }
        return null;
    }

    public function convertFullInfoFile($path, $fileName)
    {
        if ($fileName != null) {
            if (!file_exists(FCPATH . $path . $fileName)) {
                return null;
            }
            $path = base_url($path . $fileName);
            $img = file_get_contents($path);
            $data['url'] = $path;
            $data['extension'] = $this->get_image_extension($img);
            $data['data'] = base64_encode($img);

            return $data;
        }
        return null;
    }

    private function get_image_extension($image_file)
    {
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $image_file, FILEINFO_MIME_TYPE);
        $mimes =& get_mimes();
        foreach ($mimes as $key => $mime) {
            if (is_array($mime)) {
                if (in_array($mime_type, $mime, true)) {
                    return $key;
                }
            } else if ($mime === $mime_type) {
                return $key;
            }
        }
        return '';
    }


    public function content_type_base64($encode_base64)
    {
        $decode = base64_decode($encode_base64);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $decode, FILEINFO_MIME_TYPE);

        return $mime_type;
    }

    public function validatePathFile($path)
    {
        $arrayFolders = explode("/", $path);
        $fullPath = '';
        $status = array(
            'createdPath' => ''
        );

        foreach ($arrayFolders as $key => $folder) {
            $fullPath .= $key == 0 ? $folder : "/$folder";
            if(!file_exists($fullPath) && !is_dir($fullPath)){
                mkdir($fullPath);
                $status['createdPath'] .= empty($status['createdPath']) ? $folder : "/$folder";
            }
        }

        $message = empty($status['createdPath']) ? 'Done Validating Path' : "Done Validating Path, New Path Created $path";

        return $message;
    }
}

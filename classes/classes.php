<?php

require_once "db.config.php";

//reset the timezone default
date_default_timezone_set('Africa/Lagos');

class User
{

  protected $db;

  public function __construct()
  {
    $this->db = new Database();
    $this->db = $this->db->connect();
  }

  public function generate_uuid(): string
  {
    $uuid = sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
    return $uuid;
  }

  public function emailExist(string $email): bool
  {
    $query = "SELECT id from users WHERE email = ?";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$email]);
    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function registerUser(string $user_uuid, string $name, string $email, string $password): bool
  {
    $query = "INSERT INTO users (user_uuid, name, email, password, role, created_at, updated_at)" . "VALUES (?,?,?,?,?,?,?)";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$user_uuid, $name, $email, password_hash($password, PASSWORD_BCRYPT), 'MANAGER', date('Y-m-d H:i:s'), null]);

    return true;
  }

  public function loginUser(string $username, string $password): bool
  {
    $rows = $this->getUserDetails($username)->fetch(PDO::FETCH_ASSOC);
    // print_r($rows);
    // die();

    if (password_verify($password, $rows['password'])) {
      return true;
    }else {
      return false;
    }
  }

  /**
   * @param mixed $username
   * @return \PDOStatement|false|void
   */
  public function getUserDetails($username)
  {
    $query = "SELECT * from users WHERE email= ?";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$username]);

    $count_row = $stmt->rowCount();

    if ($count_row == 1) {
      return $stmt;
    }
  }

  /**
   * @param mixed $file
   * @return bool
   */
  public function validateImage($file)
  {
    $result = true;
    $part = explode(".", $file);
    $extension = end($part);

    switch (strtolower($extension)) {
      case 'jpg':
      case 'gif':
      case 'png':
      case 'jpeg':

      return $result;
    }
    $result = false;

    return $result;
  }

  /**
   * @param mixed $tmp_name
   * @return string|void
   */
  public function uploadImage($tmp_name)
  {
    if ($tmp_name) {
      $file = $tmp_name;
      $image_name = time().uniqid(). ".jpg";
      $path = "uploads/".$image_name;
  
      if (move_uploaded_file($file, $path)) {
        return $path;
      }
    }
  }

  /**
   * @param mixed $file
   * @param mixed $name
   * @param mixed $design_id
   * @param mixed $top
   * @param mixed $left
   * @param mixed $width
   * @param mixed $border
   * @param mixed $border_raduis_top_right
   * @param mixed $border_raduis_top_left
   * @param mixed $border_raduis_bottom_right
   * @param mixed $border_raduis_bottom_left
   * @param mixed $height
   * @param mixed $border_color
   * @param mixed $name_top
   * @param mixed $name_left
   * @param mixed $font_size
   * @param mixed $font_weight
   * @param mixed $font_color
   * @return true
   */
  public function finalUpload($file, $name, $design_id, $top, $left, $width, $border, $border_raduis_top_right, $border_raduis_top_left, $border_raduis_bottom_right, $border_raduis_bottom_left, $height, $border_color, $name_top, $name_left, $font_size, $font_weight, $font_color)
  {
    $sql = "INSERT INTO uploads (file_name, name, design_id, top, left_side, width, border, border_raduis_top_right, border_raduis_top_left, border_raduis_bottom_right, border_raduis_bottom_left, height, border_color, name_top, name_left, font_size, font_weight, font_color, date)" . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([$file, $name, $design_id, $top, $left, $width, $border, $border_raduis_top_right, $border_raduis_top_left, $border_raduis_bottom_right, $border_raduis_bottom_left, $height, $border_color, $name_top, $name_left, $font_size, $font_weight, $font_color, date('Y-m-d H:i:s')]);

    return true;
  }

  /**
   * @return \PDOStatement|false|void
   */
  public function fetchAllImages()
  {
    $sql = "SELECT * FROM uploads";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  /**
   * @param mixed $id
   * @return \PDOStatement|false|void
   */
  public function fetchOneImage($id)
  {
    $sql = "SELECT * FROM uploads WHERE design_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  /**
   * @param mixed $user_uuid
   * @param mixed $uploadImage
   * @param mixed $name
   * @param mixed $design_id
   * @return true
   */
  public function userUpload($user_uuid, $uploadImage, $name, $design_id)
  {
    $sql = "INSERT INTO user_uploads (user_uuid, design_id, name, image)" . "VALUES (?,?,?,?)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([$user_uuid, $design_id, $name, $uploadImage,]);

    return true;
  }

  /**
   * @param mixed $id
   * @return \PDOStatement|false|void
   */
  public function fetchUserImage($id)
  {
    $sql = "SELECT * FROM user_uploads WHERE user_uuid = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

}
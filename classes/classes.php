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

  public function getUserDetails(string $username): PDOStatement
  {
    $query = "SELECT * from users WHERE email= ?";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$username]);

    $count_row = $stmt->rowCount();

    if ($count_row == 1) {
      return $stmt;
    }
  }

  public function validateImage($file): bool
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

  public function uploadImage($tmp_name): string
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

  public function finalUpload(string $file, string $name, string $design_id, string $top, string $left, string $width, string $border, string $border_raduis_top_right, string $border_raduis_top_left, string $border_raduis_bottom_right, string $border_raduis_bottom_left, string $height, string $border_color, string $name_top, string $name_left, string $font_size, string $font_weight, string $font_color): bool
  {
    $sql = "INSERT INTO uploads (file_name, name, design_id, top, left_side, width, border, border_raduis_top_right, border_raduis_top_left, border_raduis_bottom_right, border_raduis_bottom_left, height, border_color, name_top, name_left, font_size, font_weight, font_color, date)" . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([$file, $name, $design_id, $top, $left, $width, $border, $border_raduis_top_right, $border_raduis_top_left, $border_raduis_bottom_right, $border_raduis_bottom_left, $height, $border_color, $name_top, $name_left, $font_size, $font_weight, $font_color, date('Y-m-d H:i:s')]);

    return true;
  }

  public function fetchAllImages(): PDOStatement
  {
    $sql = "SELECT * FROM uploads";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  public function fetchOneImage(string $id): PDOStatement
  {
    $sql = "SELECT * FROM uploads WHERE design_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  public function userUpload(string $user_uuid, string $uploadImage, string $name, string $design_id): bool
  {
    $sql = "INSERT INTO user_uploads (user_uuid, design_id, name, image)" . "VALUES (?,?,?,?)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([$user_uuid, $design_id, $name, $uploadImage,]);

    return true;
  }

  public function fetchUserImage(string $id): PDOStatement
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

class Auth
{

}
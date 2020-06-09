# Kiss
Kiss is a php library usefull to basic SQL request and data exploitation (image modification, password hash ...)
## Usage
### KissData
```php
$data = new KissData($data);
$data->safe_size($size, $execute=false); // Check the size and if the file is executatble
$data->hash($function=PASSWORD_BCRYPT, $cost=12); // Hash string (to protect password for example ...)
$data->is_image(); // Check if the file is an image
$data->image($size, $option=['jpg', 'jpeg', 'png', 'gif']); // Check size and extention of image
$data->image_reduction($max_size, $dst, $quality=70); // Reduct image size by selecting compression quality and max border size (don't change gif size)
$data->image_thumbnail($dst, $size=512, $quality=70); // Make thumbnail from an image
$data->str_replace_array($array, $text); // str_replace() but using array
```

### KissSQL
```php
$bdd = new kissSQL([$host, $name, $user, $pwd]);
$bdd->PDO(); // Return PDO object
$bdd->select($table, $colonum, $where = [], $limit = '', $desc = false); // SQL select request (retunr array);
$bdd->insert($table, $value); // SQL insert request 
$bdd->count($table, $where); // SQL count request (return int)
$bdd->update($table, $where, $value); // SQL update request
$bdd->delete($table, $where); // SQL delete request
$bdd->increment($table, $colonum);
decrement($table, $colonum);
```

# File Handling

## Working with Folders

Use the `FolderInfo` class to interact with folders.

```php
use AppUtils\FileHelper\FolderInfo;

$folder = FolderInfo::factory('/path/to/folder');

// List all files in the folder
foreach($folder->getSubFiles() as $file) {
    echo $file->getName() . PHP_EOL;
}
```

## Working with Files

Use the `FileInfo` class to interact with files.

```php
use AppUtils\FileHelper\FileInfo;

$file = FileInfo::factory('/path/to/file.txt');

// Read the contents of the file
$content = $file->getContents();
````

## Working with JSON files

Use the `JsonFile` class to read and write JSON files.

```php
use AppUtils\FileHelper\JSONFile;

$jsonFile = JSONFile::factory('/path/to/file.json');

// Load the data from the file into an associative array
$data = $jsonFile->getData();

// Save the data back to the file
$data['newKey'] = 'newValue';

$jsonFile->putData($data);
```


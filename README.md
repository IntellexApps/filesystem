# Filesystem helper for PHP

* Full abstraction layer.
* Supports both __files__ and __directories__.
* Easily __read__, __write__ and __search__ for files and directories. 
* All issues are casted to separate __exceptions__ for simple handling.


Examples
--------------------

##### Initialize
```php
<?php
	$file = new File('echo.txt');
	$dir = new Dir('bucket');
```

##### Basic operations works for both files and directories
```php
<?php
	$file->exists();
	$file->isReadable();
	$file->isWritable();
	$file->touch();
	$file->delete();
	
	$path = $file->getPath();
	$path = "{$file}";
```

##### File read and write
```php
<?php
	$content = $file->read();
	$file->write('overwrite content');
	$file->write('append to existing content', true);
```

##### Move a file to a directory, another file or a string path
```php
<?php
	$file->moveTo($dir);
	$file->moveTo(new File('rename.txt'));
	$file->moveTo('rename.txt');
```

##### Copy a file to a directory, another file or a string path
```php
<?php
	$file->copyTo($dir);
	$file->copyTo(new File('file-copy.txt'));
	$file->copyTo('file-copy.txt');
```

##### List directory content, with glob patterns
```php
<?php
	$dir->listDirectory();
	$dir->listDirectory('*.txt');
```

#####  Recursively find in directory, with glob patterns
```php
<?php
	$dir->find('*.xml');
```


Testing
--------------------
Go to the 'tests' directory and run:
```sh
./run-tests
```

If there are no errors, the script will silent exit with code 0.

On any error, the error will be printed out and script will end with non-zero exit code.


To do
--------------------
2. Move and copy for directories


Licence
--------------------
MIT License

Copyright (c) 2019 Intellex

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


Credits
--------------------
Script has been written by the [Intellex](https://intellex.rs/en) team.

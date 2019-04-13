<?php define('START_TIME', microtime(true));

use Intellex\Filesystem\Dir;
use Intellex\Filesystem\File;

require __DIR__ . '/../vendor/autoload.php';

// Initialize the exception handler and debug function
\Intellex\Debugger\IncidentHandler::register();
function debug($data) {
	\Intellex\Debugger\VarDump::from($data, 1);
}

// Prepare
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR);
function fail($reason) {
	echo 'Test failed:' . PHP_EOL . $reason . PHP_EOL . PHP_EOL;
	exit(1);
}

// Tests
$botFile = new File('bot');
$tests = [
	__LINE__ => [
		function () {
			return (new File(ROOT . 'read-only'))->exists();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'read-only'))->isReadable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'read-only'))->isWritable();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'read-only'))->read();
		},
		"Read only file!" ],

	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->exists();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->touch();
		},
		new File(ROOT . 'touch') ],
	__LINE__ => [
		function () {
			$time = time();
			$modified = (new File(ROOT . 'touch'))->getLastModifiedTime();
			return abs($time - $modified) < 5;
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->isReadable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->isWritable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->write('touched');
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'touch'))->read();
		},
		'touched' ],

	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->exists();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->isReadable();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->isWritable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->write('Success');
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->read();
		},
		'Success' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->write('Overwrite');
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->read();
		},
		'Overwrite' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->append('Append');
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'newFile'))->read();
		},
		'OverwriteAppend' ],

	__LINE__ => [
		function () {
			return (new File(ROOT . 'appendToEmpty'))->append("1\n2\n3\n");
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'appendToEmpty'))->read();
		},
		"1\n2\n3\n" ],

	__LINE__ => [
		function () {
			(new File(ROOT . 'appendToEmpty'))->delete();
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'appendToEmpty'))->exists();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'appendToEmpty'))->isReadable();
		},
		false ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'appendToEmpty'))->isWritable();
		},
		true ],

	__LINE__ => [
		function () {
			return (new File(ROOT . 'security.log'))->isWritable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'security.log'))->append('OK');
		} ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'security.log'))->read();
		},
		"2018-05-11 18:57:59\t\tAccess denied\n2018-05-11 18:59:12\t\tAccess granted\nOK" ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getName();
		},
		'favicon.png' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getFilename();
		},
		'favicon' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getExtension(true);
		},
		'png' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getExtension();
		},
		'png' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getMimetype();
		},
		'image/png' ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getSize();
		},
		1510 ],

	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getParent()->getPath();
		},
		__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR ],
	__LINE__ => [
		function () {
			return (new File(ROOT . 'favicon.png'))->getPath();
		},
		__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'favicon.png' ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-readable'))->exists();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-readable'))->isReadable();
		},
		false ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-readable'))->isWritable();
		},
		false ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-writable'))->exists();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-writable'))->isReadable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'non-writable'))->isWritable();
		},
		false ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->exists();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->isReadable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->isWritable();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->getName();
		},
		'bucket' ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->getPath();
		},
		__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'bucket'))->getParent()->getPath();
		},
		__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR ],
	__LINE__ => [
		function () {
			$file = new File(ROOT . 'security.log');
			(new Dir(ROOT . 'bucket'))->write($file);
			return (new Dir(ROOT . 'bucket'))->listDirectory();
		}, [
			new File(__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR . 'security.log'),
			new File(__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR . '.empty') ] ],
	__LINE__ => [
		function () {
			(new Dir(ROOT . 'bucket'))->clear();
			return (new Dir(ROOT . 'bucket'))->listDirectory();
		}, [] ],

	__LINE__ => [
		function () {
			$file = new File(ROOT . 'favicon.png');
			$exists = $file->exists();
			$filePath = $file->getPath();
			$fileParentPath = $file->getParent()->getPath();
			$file->moveTo(new Dir(ROOT . 'bucket'));
			$movePath = $file->getPath();
			$moveParentPath = $file->getParent()->getPath();
			return [ $filePath, $fileParentPath, $movePath, $moveParentPath, [ $exists, $file->exists(), (new File(ROOT . 'favicon.png'))->exists() ] ];
		},
		[
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'favicon.png',
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR . 'favicon.png',
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR,
			[ true, true, false ]
		] ],

	__LINE__ => [
		function () {
			$file = new File(ROOT . 'newFile');
			$exists = $file->exists();
			$filePath = $file->getPath();
			$fileParentPath = $file->getParent()->getPath();
			$file->copyTo(new Dir(ROOT . 'bucket'));
			$movePath = $file->getPath();
			$moveParentPath = $file->getParent()->getPath();
			return [ $filePath, $fileParentPath, $movePath, $moveParentPath, [ $exists, $file->exists(), (new File(ROOT . 'newFile'))->exists() ] ];
		},
		[
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'newFile',
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR . 'newFile',
			__DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'bucket' . DIRECTORY_SEPARATOR,
			[ true, true, true ]
		] ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'newDirectory'))->exists();
		},
		false ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'newDirectory'))->touch();
		} ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'newDirectory'))->exists();
		},
		true ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'newDirectory'))->isRoot();
		},
		false ],
	__LINE__ => [
		function () {
			return (new Dir('/'))->isRoot();
		},
		true ],
	__LINE__ => [
		function () {
			return (new Dir('/'))->getParent();
		},
		null ],

	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'search'))->listDirectory();
		}, [
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'level1.txt'),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'submarine.log')
		] ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'search'))->listDirectory('*sub*');
		}, [
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'submarine.log')
		] ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'search'))->findRecursive();
		}, [
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'level1.txt'),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'submarine.log'),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'sub1-1' . DIRECTORY_SEPARATOR),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'sub1-2' . DIRECTORY_SEPARATOR),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'txt.txt'),
			new Dir(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'sub1-2' . DIRECTORY_SEPARATOR . 'sub1-2-1' . DIRECTORY_SEPARATOR),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'sub1-2' . DIRECTORY_SEPARATOR . 'sub1-2-1' . DIRECTORY_SEPARATOR . 'kola.txt'),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR . 'file'),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR . 'file.txt')
		] ],
	__LINE__ => [
		function () {
			return (new Dir(ROOT . 'search'))->findRecursive('*.txt');
		}, [
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'level1.txt'),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'txt.txt'),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub1' . DIRECTORY_SEPARATOR . 'sub1-2' . DIRECTORY_SEPARATOR . 'sub1-2-1' . DIRECTORY_SEPARATOR . 'kola.txt'),
			new File(ROOT . DIRECTORY_SEPARATOR . 'search' . DIRECTORY_SEPARATOR . 'sub2' . DIRECTORY_SEPARATOR . 'file.txt')
		] ],

	__LINE__ => [
		function () {
			(new Dir(ROOT . 'search'))->delete();
			return (new Dir(ROOT . 'search'))->exists();
		},
		false ],

	__LINE__ => [
		function () {
			(new File(ROOT . 'non-writable' . DIRECTORY_SEPARATOR . 'no-file'))->touch();
		}, new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'non-writable' . DIRECTORY_SEPARATOR . 'no-file')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'read-only'))->touch();
		}, new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'read-only')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'read-only'))->delete();
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'read-only')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'read-only'))->write('123');
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'read-only')) ],

	__LINE__ => [
		function () {
			(new File(ROOT . 'no-file'))->moveTo(new Dir(ROOT . 'non-writable'));
		},
		new \Intellex\Filesystem\Exception\NotAFileException(new File(ROOT . 'no-file')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'read-only'))->moveTo(new Dir(ROOT . 'non-writable'));
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'read-only')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'security.log'))->moveTo(new Dir(ROOT . 'non-writable'));
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'non-writable' . DIRECTORY_SEPARATOR . 'security.log')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'no-file'))->copyTo(new Dir(ROOT . 'bucket'));
		},
		new \Intellex\Filesystem\Exception\NotAFileException(new File(ROOT . 'no-file')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'security.log'))->copyTo(new Dir(ROOT . 'non-writable'));
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'non-writable' . DIRECTORY_SEPARATOR . 'security.log')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'security.log'))->copyTo(new File(ROOT . 'read-only'));
		},
		new \Intellex\Filesystem\Exception\PathExistsException(new File(ROOT . 'read-only')) ],
	__LINE__ => [
		function () {
			(new File(ROOT . 'security.log'))->copyTo(new File(ROOT . 'read-only'), true);
		},
		new \Intellex\Filesystem\Exception\PathNotWritableException(new File(ROOT . 'read-only')) ],

	__LINE__ => [
		function () {
			(new Dir(ROOT . 'no-directory'))->delete();
		}, new \Intellex\Filesystem\Exception\NotADirectoryException(new Dir(ROOT . 'no-directory')) ],
	__LINE__ => [
		function () {
			(new Dir(ROOT . 'non-writable'))->delete();
		}, new \Intellex\Filesystem\Exception\PathNotWritableException(new Dir(ROOT . 'non-writable')) ],
	__LINE__ => [
		function () {
			(new Dir(ROOT . 'no-directory'))->listDirectory();
		}, new \Intellex\Filesystem\Exception\PathNotReadableException(new Dir(ROOT . 'no-directory')) ],
	__LINE__ => [
		function () {
			(new Dir(ROOT . 'non-readable'))->listDirectory();
		}, new \Intellex\Filesystem\Exception\PathNotReadableException(new Dir(ROOT . 'non-readable')) ],

];
foreach ($tests as $line => $test) {
	$result = null;
	$exception = null;
	try {
		$result = $test[0]();
	} catch (Exception $ex) {
		$exception = $ex;
	}

	// Make sure we have something to check
	if ($exception || key_exists(1, $test)) {

		// Handle exceptions

		if ($test[1] instanceof Exception) {
			if ($exception === null || $test[1]->getMessage() !== $exception->getMessage()) {
				$received = print_r($exception, true);
				$expected = print_r($test[1], true);
				fail("Line: {$line}\nReceived: {$received}\nExpected: {$expected}");
			}
			continue;

		} else {

			// Raise an exception
			if ($exception !== null) {
				fail("On line: {$line}: {$ex->getMessage()}");
			}

			// Compare the results
			if ($result != $test[1]) {
				$received = print_r($result, true);
				$expected = print_r($test[1], true);
				fail("Line: {$line}\nReceived: {$received}\nExpected: {$expected}");
			}
		}
	}
}

exit(0);

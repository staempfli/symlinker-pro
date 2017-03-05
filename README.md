# symlinker-pro

##### Relative symlinks creator

Small tool that creates **relative** symlinks from given paths. A file containing a bunch of paths can also be given to create multiple symlinks in a single run.

## Features

* **Relative reference** from destination to source is automatically calculated from given paths
* `relative` and `absolute` paths combination supported
* Create bunch of symlinks from file content
* Recursive symlinks using `/*` and `/**`

## Installation

### Option1: Using Composer

```
composer require "staempfli/symlinker-pro":"~1.0"
```


### Option2: Downloading .phar


```
wget https://github.com/staempfli/symlinker-pro/releases/download/<version>/symlinker-pro.phar
chmod +x ./symlinker-pro.phar
sudo mv ./symlinker-pro.phar /usr/local/bin/symlinker-pro
```

## Usage

2 possibilities:

* Single symlink:

	```
	symlinker-pro create:link <source_path> <destination_path>
	```

* Multiple symlinks from file:

	```
	symlinker-pro create:from:file <file_path>
	```

### File paths content format

A symlink definition per line with `=>` symbol to separate `source_path` and `destination_path`:

```
source_path=>destination_path
source_path2=>destination_path2
source_path3=>destination_path3
```

If your destination paths should be calculated from a different path than the current root, you can use `--dest-prefix-path` option when running `create:from:file`


## Requirements

- PHP >= 5.5

## Developers

* [Juan Alonso](https://github.com/jalogut)

Licence
-------
[GNU General Public License, version 3 (GPLv3)](http://opensource.org/licenses/gpl-3.0)

Copyright
---------
(c) 2016 Staempfli AG

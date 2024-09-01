# silverstripe-symlinktask

 Provides a Silverstripe `BuildTask` for creating symlinks to exposed resources defined in `composer.json` files. 
 This task replicates the functionality of the `composer vendor-expose` command, 
 making it useful for environments where command-line access is restricted or unavailable.
 
 
 
 ## Requirements

- SilverStripe 4 (tested with 4.13 )

## Installation

You can install the module via Composer:

```sh

composer require seppzzz/silverstripe-symlinktask

```

Alternatively, you can download the .zip file from GitHub, 
extract it, 
rename the extracted folder to "silverstripe-symlinktask", and copy it to your vendor/seppzzz/ directory.


## Documentation


To create the symlinks, navigate to:

```sh

yoursite.com/dev/tasks/CreateSymlinks

```

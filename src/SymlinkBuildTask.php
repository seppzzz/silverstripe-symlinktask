<?php

namespace Seppzzz\SymlinkTask;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Config\Configurable;

class SymlinkBuildTask extends BuildTask {

	use Configurable;

	private static $segment = 'CreateSymlinks';
	protected $title = 'Create Symlinks for Exposed Resources';
	protected $description = 'This task creates symlinks for exposed resources as defined in composer.json files.';

	public function run($request) {
		// Define the directories to search
		$directories = [
			'vendor' => BASE_PATH . '/vendor',
			'themes' => BASE_PATH . '/themes',
		];
		
		$outputVendor = '';
		$outputThemes = '';

		// Initialize an array to hold the symlink paths
		$packages = [];

		// Loop through the vendor directory
		foreach (glob($directories['vendor'] . '/*', GLOB_ONLYDIR) as $developerDir) {
			foreach (glob($developerDir . '/*', GLOB_ONLYDIR) as $moduleDir) {
				$this->processComposerFile($moduleDir, $packages, 'vendor');
			}
		}

		// Generate output for vendor symlinks
		foreach ($packages as $target => $link) {
			if (file_exists($link)) {
				if (is_link($link)) {
					$outputVendor .= "Symlink already exists: $link\n";
				} else {
					$outputVendor .= "File exists at $link, but it is not a symlink.\n";
				}
			} else {
				if (is_dir($target)) {
					if (!is_dir(dirname($link))) {
						if (mkdir(dirname($link), 0777, true)) {
							//$outputVendor .= "Directory created: " . dirname($link) . "\n";
						} else {
							$outputVendor .= "Failed to create directory: " . dirname($link) . "\n";
						}
					}

					if (symlink($target, $link)) {
						$outputVendor .= "Symlink created: $link -> $target\n";
					} else {
						$outputVendor .= "Failed to create symlink: $link\n";
					}
				} else {
					$outputVendor .= "Target is not a directory: $target\n";
				}
			}
		}

		// Clear packages to reuse for themes
		$packages = [];

		// Loop through the themes directory
		foreach (glob($directories['themes'] . '/*', GLOB_ONLYDIR) as $themeDir) {
			$this->processComposerFile($themeDir, $packages, 'themes');
		}

		// Generate output for themes symlinks
		foreach ($packages as $target => $link) {
			if (file_exists($link)) {
				if (is_link($link)) {
					$outputThemes .= "Symlink already exists: $link\n";
				} else {
					$outputThemes .= "File exists at $link, but it is not a symlink.\n";
				}
			} else {
				if (is_dir($target)) {
					if (!is_dir(dirname($link))) {
						if (mkdir(dirname($link), 0777, true)) {
							//$outputThemes .= "Directory created: " . dirname($link) . "\n";
						} else {
							$outputThemes .= "Failed to create directory: " . dirname($link) . "\n";
						}
					}

					if (symlink($target, $link)) {
						$outputThemes .= "Symlink created: $link -> $target\n";
					} else {
						$outputThemes .= "Failed to create symlink: $link\n";
					}
				} else {
					$outputThemes .= "Target is not a directory: $target\n";
				}
			}
		}

		// Combine the outputs with a separator
		$vendorSeperator = $outputVendor != "" ? "VENDOR:\n" : "";
		$themesSeperator = $outputVendor != "" ? "THEMES:\n" : "";
		$output = $vendorSeperator . $outputVendor. "\n" . $themesSeperator . $outputThemes;

		// Output the results
		echo nl2br($output);
	}

	private function processComposerFile($directory, &$packages, $type) {
		$composerFile = $directory . '/composer.json';

		if (file_exists($composerFile)) {
			$composerData = json_decode(file_get_contents($composerFile), true);

			if (isset($composerData['extra']['expose']) && is_array($composerData['extra']['expose'])) {
				foreach ($composerData['extra']['expose'] as $exposePath) {
					$target = $directory . '/' . $exposePath;
					$linkBase = ($type === 'vendor') ? '/public/_resources/vendor/' : '/public/_resources/themes/';
					$link = BASE_PATH . $linkBase . str_replace(BASE_PATH . '/' . $type . '/', '', $directory) . '/' . $exposePath;

					$packages[$target] = $link;
				}
			}
		}
	}
}





/*
namespace Seppzzz\SymlinkTask;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\Debug;


class SymlinkBuildTask extends BuildTask
{
    use Configurable;

    private static $segment = 'CreateSymlinks';

    protected $title = 'Create Symlinks for Exposed Resources';
    protected $description = 'This task creates symlinks for exposed resources as defined in composer.json files.';

    public function run($request)
    {
        // Define the directories to search
        $directories = [
            'vendor' => BASE_PATH . '/vendor',
            'themes' => BASE_PATH . '/themes',
        ];

        // Initialize an array to hold the symlink paths
        $packages = [];

        // Loop through the vendor directory
        foreach (glob($directories['vendor'] . '/*', GLOB_ONLYDIR) as $developerDir) {
            // Loop through each module in the developer's directory
            foreach (glob($developerDir . '/*', GLOB_ONLYDIR) as $moduleDir) {
                $this->processComposerFile($moduleDir, $packages, 'vendor');
            }
        }

        // Loop through the themes directory directly
        foreach (glob($directories['themes'] . '/*', GLOB_ONLYDIR) as $themeDir) {
            $this->processComposerFile($themeDir, $packages, 'themes');
        }

        // Create symlinks based on the $packages array
        $output = '';
        foreach ($packages as $target => $link) {
            if (file_exists($link) && is_link($link)) {
                $output .= "Symlink already exists: $link\n";
            } else {
                if (is_dir($target)) {
                    // Create the directory structure for the symlink if it doesn't exist
                    if (!is_dir(dirname($link))) {
                        mkdir(dirname($link), 0777, true);
                    }

                    // Create the symlink
                    if (symlink($target, $link)) {
                        $output .= "Symlink (folder alias) created: $link -> $target\n";
                    } else {
                        $output .= "Failed to create symlink: $link\n";
                    }
                } else {
                    $output .= "Target is not a directory: $target\n";
                }
            }
        }

        // Output the results
        echo nl2br($output);
    }

    private function processComposerFile($directory, &$packages, $type)
    {
        // Define the path to the composer.json file
        $composerFile = $directory . '/composer.json';

        // Check if the composer.json file exists
        if (file_exists($composerFile)) {
            // Decode the JSON content of the composer.json file
            $composerData = json_decode(file_get_contents($composerFile), true);

            // Check if the "expose" key exists in the "extra" section
            if (isset($composerData['extra']['expose']) && is_array($composerData['extra']['expose'])) {
                // Loop through each path in the "expose" array
                foreach ($composerData['extra']['expose'] as $exposePath) {
                    $target = $directory . '/' . $exposePath;
                    $linkBase = ($type === 'vendor') ? '/public/_resources/vendor/' : '/public/_resources/themes/';
                    $link = BASE_PATH . $linkBase . str_replace(BASE_PATH . '/' . $type . '/', '', $directory) . '/' . $exposePath;

                    // Add the symlink paths to the packages array
                    $packages[$target] = $link;
                }
            }
        }
    }
}
*/










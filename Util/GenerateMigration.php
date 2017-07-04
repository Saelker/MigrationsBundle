<?php

namespace Saelker\MigrationsBundle\Util;


class GenerateMigration
{
	/**
	 * @var string
	 */
	private static $template =
		'
		<?php

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_<identifier>_<description> extends MigrationFile
{
	public function up()
	{
		// this is auto-generated
	}
}
		';

	/**
	 * @param string $namespace
	 * @param string $identifier
	 * @param string $description
	 * @param string $directory
	 * @return string
	 */
	public static function generate($namespace, $identifier, $description, $directory)
	{
		$description = self::toCamelCase($description);

		$placeHolders = [
			'<namespace>',
			'<identifier>',
			'<description>',
		];

		$replacements = [
			$namespace,
			$identifier,
			$description,
		];

		$code = str_replace($placeHolders, $replacements, self::$template);
		$code = preg_replace('/^ +$/m', '', $code);

		$path = $directory . '/V_' . $identifier . '_' . $description . '.php';

		file_put_contents($path, $code);

		return $path;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	private static function toCamelCase($string)
	{
		$string = str_replace('-', ' ', $string);
		$string = str_replace('_', ' ', $string);
		$string = ucwords(strtolower($string));
		$string = str_replace(' ', '', $string);

		return $string;
	}
}
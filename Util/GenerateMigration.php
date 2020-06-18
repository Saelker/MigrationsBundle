<?php

namespace Saelker\MigrationsBundle\Util;

class GenerateMigration
{
	/**
	 * @var string
	 */
	private static $template =
		'<?php

namespace <namespace>;

use Saelker\MigrationsBundle\Util\MigrationFile;

class V_<identifier>_<description> extends MigrationFile
{
	const NOTE = "<note>";
	
	public function up()
	{
		
	}
}';

	/**
	 * @param string $namespace
	 * @param string $identifier
	 * @param string $description
	 * @param string $directory
	 * @param string $note
	 *
	 * @return string
	 */
	public static function generate(string $namespace, string $identifier, string $description, string $directory, string $note): string
	{
		$description = self::toCamelCase($description);

		$placeHolders = [
			'<namespace>',
			'<identifier>',
			'<description>',
			'<note>',
		];

		$replacements = [
			$namespace,
			$identifier,
			$description,
			str_replace("\"", "'", $note),
		];

		$code = str_replace($placeHolders, $replacements, self::$template);
		$code = preg_replace('/^ +$/m', '', $code);

		$path = $directory . '/V_' . $identifier . '_' . $description . '.php';

		file_put_contents($path, $code);

		return $path;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	private static function toCamelCase(string $string): string
	{
		$string = str_replace('-', ' ', $string);
		$string = str_replace('_', ' ', $string);
		$string = ucwords(strtolower($string));
		$string = str_replace(' ', '', $string);

		return $string;
	}

	/**
	 * @param string $directory
	 *
	 * @return null|string
	 */
	public static function getNamespaceFromDirectory(string $directory): ?string
	{
		$directories = explode('/', $directory);
		$key = array_search('src', $directories);

		$namespace = [];
		for ($i = $key + 1; $i < count($directories); $i++) {
			$namespace[] = $directories[$i];
		}

		return $key ? implode('\\', $namespace) : null;
	}
}
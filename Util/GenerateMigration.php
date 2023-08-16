<?php

namespace Saelker\MigrationsBundle\Util;

class GenerateMigration
{
	private static string $template =
		'<?php

namespace <namespace>;

use Saelker\MigrationsBundle\Util\MigrationFile;

class V_<identifier>_<description> extends MigrationFile
{
	public const NOTE = "<note>";
	
	public function up(): void
	{
		
	}
	
	public function down(): void
	{
	
	}
}';

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

	private static function toCamelCase(string $string): string
	{
		$string = str_replace(['-', '_'], ' ', $string);
		$string = ucwords(strtolower($string));

		return str_replace(' ', '', $string);
	}

	public static function getNamespaceFromDirectory(string $directory): ?string
	{
		$directories = explode('/', $directory);
		$key = array_search('src', $directories);

		$directoryCount = count($directories);
		$namespace = [];

		for ($i = $key + 1; $i < $directoryCount; $i++) {
			$namespace[] = $directories[$i];
		}

		return $key ? implode('\\', $namespace) : null;
	}
}
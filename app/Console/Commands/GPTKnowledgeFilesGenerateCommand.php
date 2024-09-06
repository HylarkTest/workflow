<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GPTKnowledgeFilesGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gpt:knowledge-files:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Concat files for custom GPT';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $files = [
            // routes/api.php and routes/web.php show the non-graphql routes
            'routes/api.php',
            'routes/web.php',

            // app/GraphQL/Utils/MappingTypeBuilder.php is an important file for understanding how the dynamic API is built
            'app/GraphQL/Utils/MappingTypeBuilder.php',

            // app/GraphQL/Queries/Features/FeatureListQuery.php has information about the schema for all features which is largely the same and can be confusing for newer devs
            'app/GraphQL/Queries/Features/FeatureListQuery.php',

            'app/Providers/AppServiceProvider.php',

            // Examples of good code:
            // app/GraphQL/Queries/CategoryQuery.php is a good example of a query class
            'app/GraphQL/Queries/CategoryQuery.php',

            // modules/markup-utils/src/Markdown.php is a good example of a concise self contained easy to read class with small methods
            'modules/markup-utils/src/Markdown.php',

            // app/Console/Commands/CurrenciesPopulateCommand.php is a good file that shows the conventions for writing a good console command
            'app/Console/Commands/CurrenciesPopulateCommand.php',

            // app/Console/Commands/DB/Health/Check/SpacesCheckCommand.php is a good file to show how health checks should work
            'app/Console/Commands/DB/Health/Check/SpacesCheckCommand.php',

            // Complex files that might need explaining:
            'app/GraphQL/PaginatorBatchLoader.php',
            'modules/actions/src/Core/ActionTranslator.php',
            'modules/actions/src/Core/ActionRecorder.php',

            // config/app.php also has some information on the tech stack
            'config/app.php',
        ];

        // Loop through the files array and generate a concatenated file with the file name as a comment before each file contents
        // Removing <?php and declare(strict_types=1); from each file if they exist
        // And beginning the file with <?php declare(strict_types=1); to ensure the file is valid PHP
        $output = "<?php declare(strict_types=1);\n";
        foreach ($files as $file) {
            $output .= "\n// $file\n";
            $output .= preg_replace('/^<\?php\s+declare\(strict_types=1\);\s*/', '', (string) file_get_contents(base_path($file)));
        }
        // Write the concatenated file to the knowledge-files directory
        file_put_contents(base_path('knowledge-files.php'), $output);

        // Go to `graphql/schema.graphql` and copy it into a file, but first check
        // for any lines beginning with '#import ' and fetch the file that is being imported
        // and add it to the concatenated file including any imports in that file recursively
        /** @var string $schema */
        $schema = file_get_contents(base_path('graphql/schema.graphql'));
        while (preg_match('/^#import (.+)$/m', $schema)) {
            /** @var string $schema */
            $schema = preg_replace_callback('/^#import (.+)$/m', function ($matches) {
                $file = $matches[1];
                $directory = dirname($file);
                /** @var string $contents */
                $contents = file_get_contents(base_path('graphql/'.$file));

                return str_replace('#import ', "#import $directory/", $contents);
            }, $schema);
        }
        file_put_contents(base_path('knowledge-schema.graphql'), $schema);

        return 0;
    }
}

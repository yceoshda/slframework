# Configuration

Configuration is located in the www/config directory.

The SL framework uses a configuration based on Json files (a default file and a custom file). You should not modify the config_default.json file, instead simply overload whatever you need in a config.json file. Both files are merged (and the result is cached to improve preformances) at runtime.

Configuration items that you put in your config.json file will always have priority on those of the config_default.json file. On the other hand, it won't take into account something that is not in the default configuration file.
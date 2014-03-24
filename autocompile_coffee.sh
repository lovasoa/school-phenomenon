# -w : watch files
# -m : generate map files (useful for debugging)
# -j : join all coffeescript files before compiling
coffee -w -j js/all.js -c js/*coffee;

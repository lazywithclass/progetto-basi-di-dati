{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php
    pkgs.apacheHttpd
  ];

  shellHook = ''
    export COMPOSER_HOME="$HOME/.composer"
    export PATH="$COMPOSER_HOME/vendor/bin:$PATH"

    # Start Apache HTTPD with the user's configuration
    echo "Starting Apache HTTPD..."
    httpd -f $PWD/httpd.conf -k start
  '';
}

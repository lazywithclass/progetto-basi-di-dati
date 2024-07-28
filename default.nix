# default.nix
{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  name = "pg-shell";

  buildInputs = [
    pkgs.php
    pkgs.postgresql
    pkgs.dbeaver
  ];

  shellHook = ''
  '';
}

# default.nix
{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  name = "pg-shell";

  buildInputs = [
    pkgs.php
    pkgs.postgresql
    pkgs.dbeaver-bin

    # for .md to .pdf conversion
    pkgs.pandoc
    pkgs.texlive.combined.scheme-basic
    pkgs.texlive.combined.scheme-medium
  ];
}

#!/bin/bash
# ----------------------------------------------------------------------
# This file is part of the Sift package.
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
# ----------------------------------------------------------------------
# This script removes trailing whitespace from files in given directory
# ----------------------------------------------------------------------
echo -e "Remove trailing spaces from files in a directory.\n"
DIRECTORY=$1
if [ -d "$DIRECTORY" ]; then
  # Control will enter here if $DIRECTORY exists.
  echo -e "Searching in '$DIRECTORY'\n"
  echo -e "please wait...\n"
  find $DIRECTORY -not \( -name .svn -prune -o -name .git -prune \) -type f -print0 | xargs -0 sed -i -e "s/[ \t]*$//"
  echo "Done."
else
  echo "Directory '$DIRECTORY' does not exist."
fi
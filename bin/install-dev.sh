#!/usr/bin/env bash

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-"127.0.0.1"}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	INST_RESTORE_DIR=true
else
	INST_RESTORE_DIR=false
fi

WP_LOCATION=`command -v wp`
PHPUNIT_LOCATION=`command -v phpunit`
PHPCOMPATINFO_LOCATION=`command -v phpcompatinfo`
TOOLS_DST_DIR="/usr/local/bin"
WPI18N_DIR="/usr/lib/wpi18n"

check_and_install_tooling() {
    if [[ $WP_LOCATION == /usr/local/bin* ]]
    then
        echo "WP-CLI exists at <$WP_LOCATION>..."
    else
        echo "WP-CLI does not exist. Installing..."
        #See https://wp-cli.org/ for more information
        curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
        chmod +x wp-cli.phar
        mv wp-cli.phar $TOOLS_DST_DIR/wp
    fi

    if [[ $PHPUNIT_LOCATION == /usr/local/bin* ]]
    then
        echo "PHP Unit exists at <$PHPUNIT_LOCATION>..."
    else
        echo "PHP Unit does not exist. Installing..."
        #See https://phpunit.de/getting-started/phpunit-5.html for more information
        wget -O phpunit-5.phar https://phar.phpunit.de/phpunit-5.phar
        chmod +x phpunit-5.phar
        mv phpunit-5.phar $TOOLS_DST_DIR/phpunit
    fi

    if [[ $PHPCOMPATINFO_LOCATION == /usr/local/bin* ]]
    then
        echo "PHP Compat Info exists at <$PHPCOMPATINFO_LOCATION>..."
    else
        echo "PHP Compat Info does not exist. Installing..."
        #See http://php5.laurent-laville.org/compatinfo/manual/current/en/getting-started.html for more information
        wget http://bartlett.laurent-laville.org/get/phpcompatinfo-5.0.12.phar
        chmod +x phpcompatinfo-5.0.12.phar
        mv phpcompatinfo-5.0.12.phar $TOOLS_DST_DIR/phpcompatinfo
    fi

    if [ ! -d $WPI18N_DIR ]
    then
        echo "WPI18N directory does not exist at $WPI18N_DIR. Creating..."
        mkdir $WPI18N_DIR
    fi

    if [[ ! `ls -A $WPI18N_DIR` ]]
    then
        echo "Checking out WPI18N library..."
        svn co http://i18n.svn.wordpress.org/tools/trunk/ $WPI18N_DIR
    fi
}

setup_unit_testing() {
    wp scaffold plugin-tests abp01-travel-tech-box
    if [[ -f "./bin/install-wp-tests.sh" ]] 
    then
        ./bin/install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION" "$SKIP_DB_CREATE"
    fi
}

check_and_install_tooling
setup_unit_testing

if [ "$INST_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi
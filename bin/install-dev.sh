#!/usr/bin/env bash

POSITIONAL=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --db-name)
            DB_NAME="$2"
            shift 2
            ;;
        --db-name=*)
            DB_NAME="${1#*=}"
            shift
            ;;

        --db-user)
            DB_USER="$2"
            shift 2
            ;;
        --db-user=*)
            DB_USER="${1#*=}"
            shift
            ;;

        --db-pass)
            DB_PASS="$2"
            shift 2
            ;;
        --db-pass=*)
            DB_PASS="${1#*=}"
            shift
            ;;

        --db-host)
            DB_HOST="$2"
            shift 2
            ;;
        --db-host=*)
            DB_HOST="${1#*=}"
            shift
            ;;

        --wp-version)
            WP_VERSION="$2"
            shift 2
            ;;
        --wp-version=*)
            WP_VERSION="${1#*=}"
            shift
            ;;

        --skip-db-create)
            SKIP_DB_CREATE="$2"
            shift 2
            ;;
        --skip-db-create=*)
            SKIP_DB_CREATE="${1#*=}"
            shift
            ;;

        --skip-wp-tests-scaffold)
            SKIP_WP_TESTS_SCAFFOLD="$2"
            shift 2
            ;;
        --skip-wp-tests-scaffold=*)
            SKIP_WP_TESTS_SCAFFOLD="${1#*=}"
            shift
            ;;

        --)
            shift
            POSITIONAL+=("$@")
            break
            ;;

        -*)
            echo "Unknown argument: $1" >&2
            exit 1
            ;;

        *)
            POSITIONAL+=("$1")
            shift
            ;;
    esac
done

DB_NAME="${DB_NAME:-${POSITIONAL[0]:-}}"
DB_USER="${DB_USER:-${POSITIONAL[1]:-}}"
DB_PASS="${DB_PASS:-${POSITIONAL[2]:-}}"
DB_HOST="${DB_HOST:-${POSITIONAL[3]:-127.0.0.1}}"
WP_VERSION="${WP_VERSION:-${POSITIONAL[4]:-latest}}"
SKIP_DB_CREATE="${SKIP_DB_CREATE:-${POSITIONAL[5]:-false}}"
SKIP_WP_TESTS_SCAFFOLD="${SKIP_WP_TESTS_SCAFFOLD:-${POSITIONAL[6]:-true}}"

if [ $SKIP_WP_TESTS_SCAFFOLD != "true" ]
then
    if [[ -z "$DB_NAME" || -z "$DB_USER" || -z "$DB_NAME" || -z "$DB_HOST" ]]
    then
        echo "Database parameters required when scaffolding tests also required." >&2
        exit 1
    fi
fi

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
TOOLS_DST_DIR="/usr/local/bin"
WPI18N_DIR="/usr/lib/wpi18n"

check_and_install_composer() {
    if command -v composer > /dev/null 2>&1
    then
        echo "Composer exists at <$(command -v composer)>..."
    else
        echo "Composer does not exist. Installing..."

        COMPOSER_INSTALLER="composer-setup.php"
        COMPOSER_EXPECTED_SIGNATURE="$(curl -fsSL https://composer.github.io/installer.sig)"

        curl -fsSL https://getcomposer.org/installer -o "$COMPOSER_INSTALLER"
        COMPOSER_ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', '$COMPOSER_INSTALLER');")"

        if [[ "$COMPOSER_EXPECTED_SIGNATURE" != "$COMPOSER_ACTUAL_SIGNATURE" ]]
        then
            echo "Invalid Composer installer signature." >&2
            rm -f "$COMPOSER_INSTALLER"
            exit 1
        fi

        php "$COMPOSER_INSTALLER" \
            --install-dir="$TOOLS_DST_DIR" \
            --filename=composer
        rm -f "$COMPOSER_INSTALLER"

        if ! command -v composer > /dev/null 2>&1
        then
            echo "Composer was installed but cannot be invoked directly." >&2
            exit 1
        fi
    fi
}

install_vendor_dependencies() {
    composer install
    chmod +x vendor/bin/phpunit
}

check_and_install_wp_stuff() {
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

check_and_install_tooling() {
    check_and_install_composer
    check_and_install_wp_stuff
    install_vendor_dependencies
}

setup_unit_testing() {
    if [ $SKIP_WP_TESTS_SCAFFOLD != "true" ]
    then
        wp scaffold plugin-tests abp01-travel-tech-box --allow-root
	fi
    
    if [[ -f "./bin/install-wp-tests.sh" ]] 
    then
        ./bin/install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION" "$SKIP_DB_CREATE"
    fi
}

export WP_DB_HOST_ENV=$DB_HOST
export WP_DEBUG_ENV=false

check_and_install_tooling
setup_unit_testing

if [ "$INST_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi

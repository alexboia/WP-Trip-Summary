#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat <<'EOF'
Usage:
  ./bin/install-dev.sh [options]
  ./bin/install-dev.sh [DB_NAME] [DB_USER] [DB_PASS] [DB_HOST] [WP_VERSION] [SKIP_DB_CREATE] [SKIP_WP_TESTS_SCAFFOLD]

Options:
  --db-name VALUE                    Test database name (required)
  --db-user VALUE                    Test database user (required)
  --db-pass VALUE                    Test database password; may be empty
  --db-host VALUE                    Database host (default: 127.0.0.1)
  --wp-version VALUE                 WordPress test version (default: latest)
  --skip-db-create VALUE             Whether to skip database creation (default: true)
  --skip-wp-tests-scaffold VALUE     Whether to skip WP-CLI scaffolding (default: true)
  -h, --help                         Show this help and exit

Named options also accept the --option=value form and may be mixed with
positional arguments. Named options take precedence over positional arguments.

Examples:
  ./bin/install-dev.sh test_db root '' 172.20.0.1 latest false true
  ./bin/install-dev.sh --db-name=test_db --db-user=root --db-pass='' \
    --db-host=172.20.0.1 --skip-wp-tests-scaffold=false
EOF
}

POSITIONAL=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --db-name|--db-user|--db-pass|--db-host|--wp-version|--skip-db-create|--skip-wp-tests-scaffold)
            if [[ $# -lt 2 ]]
            then
                echo "Missing value for argument '$1'." >&2
                exit 1
            fi
            ;;
    esac

    case "$1" in
        -h|--help)
            usage
            exit 0
            ;;

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

if [[ ${#POSITIONAL[@]} -gt 7 ]]
then
    echo "Too many positional arguments." >&2
    echo "Run '$0 --help' for usage." >&2
    exit 1
fi

DB_NAME="${DB_NAME-${POSITIONAL[0]:-}}"
DB_USER="${DB_USER-${POSITIONAL[1]:-}}"
DB_PASS="${DB_PASS-${POSITIONAL[2]:-}}"
DB_HOST="${DB_HOST-${POSITIONAL[3]:-127.0.0.1}}"
WP_VERSION="${WP_VERSION-${POSITIONAL[4]:-latest}}"
SKIP_DB_CREATE="${SKIP_DB_CREATE-${POSITIONAL[5]:-true}}"
SKIP_WP_TESTS_SCAFFOLD="${SKIP_WP_TESTS_SCAFFOLD-${POSITIONAL[6]:-true}}"

# Boolean sanity checks
for BOOLEAN_VALUE in "$SKIP_DB_CREATE" "$SKIP_WP_TESTS_SCAFFOLD"
do
    if [[ "$BOOLEAN_VALUE" != "true" && "$BOOLEAN_VALUE" != "false" ]]
    then
        echo "Boolean options must be either 'true' or 'false'; received '$BOOLEAN_VALUE'." >&2
        exit 1
    fi
done

# Only check DB args if DB creation is not skipped
if [[ "$SKIP_DB_CREATE" != "true" ]]
then
    if [[ -z "$DB_NAME" || -z "$DB_USER" || -z "$DB_HOST" ]]
    then
        echo "Database name, user and host are required." >&2
        echo "Run '$0 --help' for usage." >&2
        exit 1
    fi
fi

# Always run relative to the plug-in root, regardless of the caller's directory.
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
pushd "$SCRIPT_DIR/.." > /dev/null
trap 'popd > /dev/null' EXIT

WP_ROOT_DIR="$(cd "$SCRIPT_DIR/../../../.." && pwd)"
TOOLS_DST_DIR="/usr/local/bin"
WPI18N_DIR="/usr/lib/wpi18n"

require_command() {
    if ! command -v "$1" > /dev/null 2>&1
    then
        echo "Required command '$1' is not installed." >&2
        exit 1
    fi
}

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
    composer install --no-interaction

    if [[ ! -f vendor/bin/phpunit ]]
    then
        echo "Composer did not install the expected vendor/bin/phpunit executable." >&2
        exit 1
    fi

    chmod +x vendor/bin/phpunit
}

check_and_install_wp_stuff() {
    if command -v wp > /dev/null 2>&1
    then
        echo "WP-CLI exists at <$(command -v wp)>..."
    else
        echo "WP-CLI does not exist. Installing..."
        #See https://wp-cli.org/ for more information
        curl -fsSLO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
        chmod +x wp-cli.phar
        mv wp-cli.phar "$TOOLS_DST_DIR/wp"
    fi

    if [[ ! -d "$WPI18N_DIR" ]]
    then
        echo "WPI18N directory does not exist at $WPI18N_DIR. Creating..."
        mkdir -p "$WPI18N_DIR"
    fi

    if [[ -z "$(ls -A "$WPI18N_DIR")" ]]
    then
        echo "Checking out WPI18N library..."
        svn co https://i18n.svn.wordpress.org/tools/trunk/ "$WPI18N_DIR"
    fi
}

check_wp_config_wired_to_getenv() {
    local wp_config_path
    local variable_name
    local missing_variables=()

    wp_config_path="$(wp config path --path="$WP_ROOT_DIR" --allow-root)"

    if [[ "$wp_config_path" != /* ]]
    then
        wp_config_path="$WP_ROOT_DIR/$wp_config_path"
    fi

    if [[ ! -f "$wp_config_path" ]]
    then
        echo "Could not find the WordPress configuration file at '$wp_config_path'." >&2
        exit 1
    fi

    for variable_name in WP_DB_HOST_ENV WP_DEBUG_ENV
    do
        if ! grep -Eq "getenv[[:space:]]*\\([[:space:]]*['\"]${variable_name}['\"]" "$wp_config_path"
        then
            missing_variables+=("$variable_name")
        fi
    done

    if [[ ${#missing_variables[@]} -gt 0 ]]
    then
        echo "WordPress configuration '$wp_config_path' is not wired to getenv() for:" >&2
        printf '  - %s\n' "${missing_variables[@]}" >&2
        exit 1
    fi

    echo "WordPress configuration <$wp_config_path> uses WP_DB_HOST_ENV and WP_DEBUG_ENV..."
}

check_and_install_tooling() {
    require_command php
    require_command curl
    require_command svn

    if [[ "$SKIP_DB_CREATE" != "true" ]]
    then
        require_command mysqladmin
    fi

    check_wp_config_wired_to_getenv
    check_and_install_composer
    install_vendor_dependencies
    check_and_install_wp_stuff
}

setup_unit_testing() {
    if [[ "$SKIP_WP_TESTS_SCAFFOLD" != "true" ]]
    then
        wp scaffold plugin-tests abp01-travel-tech-box --allow-root
    else 
        echo "Skipping WP unit tests setup..."
	fi
    
    if [[ -f "./bin/install-wp-tests.sh" ]] 
    then
        ./bin/install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION" "$SKIP_DB_CREATE"
    fi
}

# The WP Config file should be wired to receive these via getenv();
export WP_DB_HOST_ENV="$DB_HOST"
export WP_DEBUG_ENV=false

check_and_install_tooling
setup_unit_testing

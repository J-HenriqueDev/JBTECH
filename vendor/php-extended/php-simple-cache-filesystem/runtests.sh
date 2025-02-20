#!/bin/bash
set -eu
IFS=$'\n\t'

CURRDIR="$(dirname "$0")"
CURRDIR="$(realpath "$CURRDIR")"
PAREDIR="$(realpath "$CURRDIR/..")"
cd "$CURRDIR"

# {{{ FUNCTIONS

# Writes into the console the date and a message
# @var $1 string the message to write
log() {
	echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Writes into the console the given string in WHITE
# @var $1 string the message to write
log_info() {
	log "[  INFO   ] $1"
}

# Writes into the console the given string in RED
# @var $1 string the message to write
log_error() {
	log "[  \033[31mERROR\033[0m  ] $1"
}

# Writes into the console the given string in GREEN
# @var $1 string the message to write
log_success() {
	log "[ \033[32mSUCCESS\033[0m ] $1"
}

# Writes into the console the given string in YELLOW
log_warning() {
	log "[ \033[33mWARNING\033[0m ] $1"
}

# Exits prematurely if the $1 arg is not zero and if this script is running in CI
# @var $1 integer the exit code of previous function call
exit_if_failed_outside_ci() {
	if [ "$RUN_IN_CI" == "0" ]
	then
		if [ "$1" != "0" ]
		then
			exit "$1"
		fi
	fi
}

# Function to copy the file at $1 to $2 if it exists at the source
# @var $1 string source the file to check if it exists
# @var $2 string dest the path where to copy it if we found it
copy_if_exists() {
	if [ "$RUN_IN_CI" == "1" ]
	then
		if [ -f "$1" ]
		then
			cp "$1" "$2"
		fi
	fi
}

# Function to check the availability of a given binary
# @var $1 string the name of the binary to check
check_install() {
	local RES
	
	set +e
	command -v "$1" > /dev/null 2>&1
	RES=$?
	set -e
	if [ "$RES" != "0" ]
	then
		log_error "Failed to find $1, please install it"
		exit 1
	fi
}

# Function to download from $1 and store the result to $2
# @var $1 string the url to download
# @var $2 string the local path of the file to save
download() {
	check_install "curl"
	log_info "DOWNLOADING : $1"
	log_info "WRITING TO  : $2"
	curl --location --progress-bar --fail --show-error "$1" --output "$2"
}

# Function to get the full release url for the given library
# @var $1 string the vendor name
# @var $2 string the library name
get_release_url() {
	local API_URL
	local RELEASE_URL
	
	if [ "$2" == "phpunit" ]
	then
		echo "https://phar.phpunit.de/phpunit-9.phar"
	else
		check_install "curl"
		check_install "jq"
		check_install "sed"
		API_URL="https://api.github.com/repos/$1/$2/releases"
		RELEASE_URL=$(curl --location --progress-bar --fail --show-error "$API_URL" | jq '[.[]|.assets|.[]|.browser_download_url][0]' | sed 's/"//g')
		echo "$RELEASE_URL"
	fi
}

# Ensures that the given phar file is correctly installed
# @var $1 string the path of the file where to search it on the file system if available
# @var $2 string the path where the file should be executed
# @var $3 string the package vendor name in the github url
# @var $4 string the package library name in the github url
phar_install() {
	local OLDPSTN
	local RELEASE_URL
	
	check_install "find"
	copy_if_exists "$1" "$2"
	
	set +e
	OLDPSTN=$(find "$2" -mtime +7 -print > /dev/null 2>&1)
	set -e
	if [ ! -f "$2" ] || [ -n "$OLDPSTN" ]
	then
		RELEASE_URL=$(get_release_url "$3" "$4")
		download "$RELEASE_URL" "$2"
	else
		log_warning "DO NOT INSTALL $3/$4 : not old enough"
	fi
}

# Tries to install the dependancies with composer
composer_install() {
	local COMPOSER_VERB
	local COMPOSER_ING
	local RET
	
	printf "\n"
	
	phar_install "/composer.phar" "$PAREDIR/composer.phar" "composer" "composer"
	
	if [ ! -f "$CURRDIR/vendor/autoload.php" ]
	then
		COMPOSER_VERB="install"
		COMPOSER_ING="INSTALLING"
	else
		COMPOSER_VERB="update"
		COMPOSER_ING="UPDATING"
	fi
	
	log_info "$COMPOSER_ING composer dependancies"
	set +e
	php "$PAREDIR/composer.phar" "$COMPOSER_VERB" --ansi --no-interaction --no-progress --prefer-dist
	RET=$?
	set -e
	
	return $RET
}

# Tries to install composer dependancies multiple times
# @var $1 integer the number of retries
composer_install_retry() {
	local RET
	local MAXLOOP
	
	composer_install
	RET=$?
	
	MAXLOOP=$(($1))
	if [ -z ${1+x} ]
	then
		MAXLOOP=3
	fi
	
	LOOP=0
	while [[ $LOOP < $MAXLOOP && "$RET" != "0" ]]
	do
		sleep "$((39 * (LOOP + 1)))s"
		LOOP=$((LOOP + 1))
		composer_install
		RET=$?
	done
	
	return $RET
}

# Rebuild the composer.json ordering all of its keys in the right order
rebuild_composer_json() {
	if [ "$RUN_IN_CI" == "0" ]
	then
		
		printf "\n"
		# we dont need to rebuild composer.json hence dont need jq when in CI
		
		log_info "REORDERING : $CURRDIR/composer.json"
		CURRCOMP=$(cat "$CURRDIR/composer.json")
		# reorder composer.json according to schema
		# https://getcomposer.org/doc/04-schema.md
		# except minimum stability
		# pretty print with tabs
		# then ignore null fields
		# then add space before colon
		echo "$CURRCOMP" | jq --tab '{ 
			name: .name,
			description: .description,
			version: .version,
			type: .type,
			keywords: .keywords,
			homepage: .homepage,
			readme: .readme,
			time: .time,
			license: .license,
			authors: .authors,
			support: .support,
			funding: .funding,
			require: .require,
			"require-dev": ."require-dev",
			conflict: .conflict,
			replace: .replace,
			provide: .provide,
			suggest: .suggest,
			autoload: .autoload,
			"autoload-dev": ."autoload-dev",
			"include-path": ."include-path",
			"target-dir": ."target-dir",
			"prefer-stable": ."prefer-stable",
			repositories: .repositories,
			config: .config,
			scripts: .scripts,
			extra: .extra,
			bin: .bin,
			archive: .archive,
			abandoned: .abandoned,
			"non-feature-branches": ."non-feature-branches"
		} | del(.[] | nulls)' | sed 's/":/" :/g' | head -c -1 > "$CURRDIR/composer.json"
	fi
}

# Generates all the test files corresponding to a 1st level class in src
generate_test_files() {
	
	# migrate the old test folder to the tests folder
	if [ -d "$CURRDIR/test" ]
	then
		if [ ! -d "$CURRDIR/tests" ]
		then
			mv -v "$CURRDIR/test" "$CURRDIR/tests"
		else
			mv -v "$CURRDIR"/test/* "$CURRDIR/tests/"
			rmdir "$CURRDIR/test"
		fi
	fi
	
	# generate the standard tests folder
	if [ ! -d "$CURRDIR/tests" ]
	then
		mkdir "$CURRDIR/tests"
	fi
	
	# for each class file in src generate a standardized test class
	for FILE in $(find ./src -maxdepth 1 -name '*.php')
	do
		TESTFILENAME=$(basename "$FILE")
		OBJCLASSNAME="${TESTFILENAME:0:-4}"
		TESTCLASSNAME="${OBJCLASSNAME}Test"
		TESTFILENAME="$TESTCLASSNAME.php"
		TESTFILEPATH=$(realpath "./tests/$TESTFILENAME")
		
		echo "[$(date '+%Y-%m-%d %H:%M:%S')] Checking test file $TESTFILENAME"
		[ -f "$TESTFILEPATH" ] && continue
		
		NAMESPACE=$(jq '.autoload."psr-4" | keys[0]' "$CURRDIR/composer.json" | sed 's/"//g' | sed 's/\\\\/\\/g' | sed 's/\\$//g')
		
		cat > "$TESTFILEPATH" <<EOF
<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use $NAMESPACE\\$OBJCLASSNAME;

/**
 * $TESTCLASSNAME test file.
 * 
 * @author Anastaszor
 * @covers \\$NAMESPACE\\$OBJCLASSNAME
 */
class $TESTCLASSNAME extends TestCase
{
	
	/**
	 * The object to test.
	 * 
	 * @var $OBJCLASSNAME
	 */
	protected $OBJCLASSNAME \$_object;
	
	public function testToString() : void
	{
		\$this->assertEquals(get_class(\$this->_object).'@'.spl_object_hash(\$this->_object), \$this->_object->__toString());
	}
	
	/**
	 * {@inheritdoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	protected function setUp() : void
	{
		\$this->_object = new $OBJCLASSNAME();
	}
	
}
EOF
	
	done
}


# Checks whether the given piece of code exists in the codebase of this library
# and fails if this is the case
# @var $1 string the human readable value of the code checked for
# @var $2 string the regex for grep to be found
# @var $3 string the message in case this code is detected for correction
check_code() {
	log_info "CHECK FOR $1 VALUES IN SOURCE CODE"
	set +e
	grep -q -r "$2" ./src --include="*.php"
	RUN_RESULT=$((1 - $?))
	set -e
	if [ "$RUN_IN_CI" == "0" ]
	then
		if [ "$RUN_RESULT" != "0" ]
		then
			log_error "$3"
			exit 10
		fi
	fi
	return $RUN_RESULT
}

# Runs the php-cs-fixer binary and returns the result
run_phpcsfixer() {
	RUN_RESULT_PHPCSFIXER=0
	if [ "$PHPVERSIONINT" == "81" ]
	then
		
		printf "\n"
		phar_install "/vendor/bin/php-cs-fixer" "$PAREDIR/php-cs-fixer.phar" "FriendsOfPHP" "PHP-CS-Fixer"
		
		log_info "RUN PHP CS FIXER"
		set +e
		if [ "$RUN_IN_CI" == "0" ]
		then
			php "$PAREDIR/php-cs-fixer.phar" fix -vvv --allow-risky=yes
		else
			php "$PAREDIR/php-cs-fixer.phar" fix -v --allow-risky=yes --dry-run
		fi
		RUN_RESULT_PHPCSFIXER=$?
		set -e
		exit_if_failed_outside_ci $RUN_RESULT_PHPCSFIXER
		
	else
		log_info "SKIPPED php-cs-fixer : not php 8.1"
	fi
	printf "\n"
	
	return $RUN_RESULT_PHPCSFIXER
}

# Runs the phpstan binary and returns the result
run_phpstan() {
	phar_install "/vendor/bin/phpstan" "$PAREDIR/phpstan.phar" "phpstan" "phpstan"
	
	log_info "RUN PHPSTAN"
	set +e
	php "$PAREDIR/phpstan.phar" --version
	php "$PAREDIR/phpstan.phar" analyse --configuration="$CURRDIR/phpstan.neon" --error-format=gitlab --memory-limit 2G
	RUN_RESULT_PHPSTAN=$?
	set -e
	exit_if_failed_outside_ci $RUN_RESULT_PHPSTAN
	printf "\n"
	
	return $RUN_RESULT_PHPSTAN
}

# Runs the psalm binary and returns the result
run_psalm() {
	RUN_RESULT_PSALM=0
	if (( PHPVERSIONINT >= 74 ))
	then
		phar_install "/vendor/bin/psalm" "$PAREDIR/psalm.phar" "vimeo" "psalm"
		
		log_info "CLEAR PSALM CACHE"
		rm -rf ~/.cache/psalm
		log_info "RUN PSALM"
		set +e
		php "$PAREDIR/psalm.phar" --version
		php "$PAREDIR/psalm.phar" --config="$CURRDIR/psalm.xml" --output-format=console --long-progress --stats --show-info=true --php-version="$PHPVERSION"
		RUN_RESULT_PSALM=$?
		set -e
		exit_if_failed_outside_ci $RUN_RESULT_PSALM
		printf "\n"
	fi
	
	return $RUN_RESULT_PSALM
}

# Runs the phpmd binary and returns the result
run_phpmd() {
	phar_install "/vendor/bin/phpmd" "$PAREDIR/phpmd.phar" "phpmd" "phpmd"
	
	log_info "RUN PHPMD"
	set +e
	php "$PAREDIR/phpmd.phar" --version
	php "$PAREDIR/phpmd.phar" "$CURRDIR/src" ansi "$CURRDIR/phpmd.xml"
	RUN_RESULT_PHPMD=$?
	set -e
	exit_if_failed_outside_ci $RUN_RESULT_PHPMD
	printf "\n"
	
	return $RUN_RESULT_PHPMD
}

# Runs the phpunit binary and returns the result
run_phpunit() {
	rm -rf "$CURRDIR/build/coverage"
	phar_install "/vendor/bin/phpunit" "$PAREDIR/phpunit.phar" "sebastianbergmann" "phpunit"
	
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] RUN PHPUNIT"
	set +e
	php "$PAREDIR/phpunit.phar" --configuration "$CURRDIR/phpunit.xml" --coverage-text --verbose
	RUN_RESULT_PHPUNIT=$?
	set -e
	exit_if_failed_outside_ci $RUN_RESULT_PHPUNIT 
	rm -f "$CURRDIR/.phpunit.result.cache"
	printf "\n"
	
	return $RUN_RESULT_PHPUNIT
}

# }}} END FUNCTIONS


# {{{ SCRIPT BEGINS HERE

check_install "cat"
check_install "curl"
check_install "grep"
check_install "head"
check_install "jq"
check_install "php"
check_install "sed"
check_install "wc"
printf "\n"

# @var PHPVERSION string the php version (we get "A.B")
# php -v :: PHP A.B.C (cli) (built: Aug XX YYYY HH:MM:SS) ( NTS )
PHPVERSION=$(php -r "echo \PHP_MAJOR_VERSION.'.'.\PHP_MINOR_VERSION;")
PHPVERSIONINT=$(($(php -r "echo \PHP_MAJOR_VERSION.\PHP_MINOR_VERSION;")))
log_info "RUNNING ON PHP : (string) $PHPVERSION / (int) $PHPVERSIONINT"

# @var RUN_IN_CI boolean whether this script runs in CI (gitlab...)
RUN_IN_CI=0

# argument management loop
# https://stackoverflow.com/questions/192249/how-do-i-parse-command-line-arguments-in-bash
for arg in "$@"
do
	case $arg in
		--ci*) RUN_IN_CI=1 ;;
		*)                 ;;
	esac
	shift # remove arg from "$@" and reorder $ positions
done

# {{{ actual job done
rebuild_composer_json
composer_install_retry 3
EXIT_CODE=0

if [[ $(cat composer.json | jq '.name' | grep -e '-interface' | wc -l) == "0" ]]
then
	generate_test_files
fi

run_phpcsfixer
RUN_RESULT_PHPCSFIXER=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_PHPCSFIXER))

# If the library contains -cache- in its name, it uses psr/cache or psr/simple-cache
# interfaces libraries. Such libraries enforce the mixed type as output, so ignore
# the ban mixed requirement for such libraries
if [[ $(cat composer.json | jq '.name' | grep -e '-cache-' | wc -l) == "0" ]]
then
	check_code 'MIXED       ' 'mixed' "There are still 'mixed' values in the source code, please expand it with null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>"
	RUN_RESULT_BAN_MIXED=$?
else
	RUN_RESULT_BAN_MIXED=0
fi
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_MIXED))

check_code ': SELF      ' ': self' "There are still ': self' return types values in the code, change it to the interface name."
RUN_RESULT_BAN_SELF=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_SELF))

check_code '@return X[] ' '@return .*\[\]' "There are still '@return X[]' return type values in the code, change it to array<integer|string, X>."
RUN_RESULT_BAN_RETURN_TAB=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_RETURN_TAB))

check_code '@var X[]    ' '@var .*\[\]' "There are still '@var X[]' variable type values in the code, change it to array<integer|string, X>."
RUN_RESULT_BAN_VAR_TAB=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_VAR_TAB))

check_code '@param X[]  ' '@param .*\[\]' "There are still '@param X[]' parameter type values in the code, change it to array<integer|string, X>."
RUN_RESULT_BAN_PARAM_TAB=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_PARAM_TAB))

check_code '?array      ' '\?array' "There are still '?array' values in the code, make them non nullable."
RUN_RESULT_BAN_NULL_ARR=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_NULL_ARR))

check_code '?iterable   ' '\?iterable' "There are still '?iterable' values in the code, make them non nullable."
RUN_RESULT_BAN_NULL_IBL=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_NULL_IBL))

check_code '?Iterator   ' '\?Iterator' "There are still '?Iterator' values in the code, make them non nullable."
RUN_RESULT_BAN_NULL_ITE=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_NULL_ITE))

check_code '?Traversable' '\?Traversable' "There are still '?Traversable' values in the code, make them non nullable."
RUN_RESULT_BAN_NULL_TRV=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_BAN_NULL_TRV))

run_phpstan
RUN_RESULT_PHPSTAN=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_PHPSTAN))

run_psalm
RUN_RESULT_PSALM=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_PSALM))

run_phpmd
RUN_RESULT_PHPMD=$?
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_PHPMD))

if [[ $(cat composer.json | jq '.name' | grep -e '-interface' | wc -l) == "0" ]]
then
	run_phpunit
	RUN_RESULT_PHPUNIT=$?
else
	RUN_RESULT_PHPUNIT=0
fi
EXIT_CODE=$((EXIT_CODE + RUN_RESULT_PHPUNIT))
# }}}

# https://stackoverflow.com/questions/4842424/list-of-ansi-color-escape-sequences
printf "\n"
PASSED="\033[32mSUCCESS\033[0m"
FAILED="\033[33mFAILED\033[0m"
log_info "---- CI/CD SCRIPT RESUME ----"
RESULT=$([ $RUN_RESULT_BAN_MIXED == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN MIXED        : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_SELF == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN RETURN SELF  : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_RETURN_TAB == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN RETURN TAB[] : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_VAR_TAB == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN VAR TAB[]    : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_PARAM_TAB  == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN PARAM TAB[]  : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_NULL_ARR == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN ?array       : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_NULL_IBL == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN ?iterable    : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_NULL_ITE == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN ?Iterator    : $RESULT"
RESULT=$([ $RUN_RESULT_BAN_NULL_TRV == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT BAN ?Traversable : $RESULT"
RESULT=$([ $RUN_RESULT_PHPCSFIXER == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT PHPCSFIXER       : $RESULT"
RESULT=$([ $RUN_RESULT_PHPMD == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT PHPMD            : $RESULT"
RESULT=$([ $RUN_RESULT_PHPSTAN == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT PHPSTAN          : $RESULT"
RESULT=$([ $RUN_RESULT_PSALM == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT PSALM            : $RESULT"
RESULT=$([ $RUN_RESULT_PHPUNIT == 0 ] && echo -e "$PASSED" || echo -e "$FAILED")
log_info "RUN RESULT PHPUNIT          : $RESULT"
log_info "---- CI/CD END OF SCRIPT ----"
printf "\n"

log_info "EXIT CODE : $EXIT_CODE"
exit $EXIT_CODE

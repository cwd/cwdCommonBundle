<?php
namespace Cwd\CommonBundle\Options;

/**
 * Interface to indicate that a class supports the methods also provided by ValidatedOptionsTrait
 *
 * @package Cwd\CommonBundle
 * @author David Herrmann <office@web-emerge.com>
 */
interface ValidatedOptionsInterface
{
    /**
     * Get the value of the given option, validated by the OptionsResolver.
     * Validation is triggered automatically when this method is called.
     *
     * If the given option was not configured, an InvalidOptionException will be thrown.
     * If you do not want that exception to be thrown, try getOptionOrDefault() instead.
     *
     * @throws InvalidOptionException if the option name was not defined
     *
     * Exceptions that may be thrown by the OptionsResolver itself:
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the
     *                                   specified validation rules
     * @throws MissingOptionsException   If a required option is missing
     * @throws OptionDefinitionException If there is a cyclic dependency between
     *                                   lazy options and/or normalizers
     * @throws NoSuchOptionException     If a lazy option reads an unavailable option
     * @throws AccessException           If called from a lazy option or normalizer
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name);

    /**
     * Get the value of the given option, validated by the OptionsResolver.
     * Validation is triggered automatically when this method is called.
     *
     * Other than getOption() this method allows for a default value that will
     * be returned if the option has not been configured.
     *
     * Exceptions that may be thrown by the OptionsResolver itself:
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the
     *                                   specified validation rules
     * @throws MissingOptionsException   If a required option is missing
     * @throws OptionDefinitionException If there is a cyclic dependency between
     *                                   lazy options and/or normalizers
     * @throws NoSuchOptionException     If a lazy option reads an unavailable option
     * @throws AccessException           If called from a lazy option or normalizer
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOptionOrDefault($name, $default = null);

    /**
     * Check if the given option has been configured.
     *
     * Exceptions that may be thrown by the OptionsResolver itself:
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the
     *                                   specified validation rules
     * @throws MissingOptionsException   If a required option is missing
     * @throws OptionDefinitionException If there is a cyclic dependency between
     *                                   lazy options and/or normalizers
     * @throws NoSuchOptionException     If a lazy option reads an unavailable option
     * @throws AccessException           If called from a lazy option or normalizer
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name);
}

<?php
namespace Cwd\CommonBundle\Options;

use Cwd\CommonBundle\Exception\InvalidOptionException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Trait to add simple options handling to any class that needs it.
 * Usage:.
 *
 * * add the trait to your class: `use ValidatedOptionsTrait`
 * * set your raw option values by overriding setOptions()
 * * optionally set options using setRuntimeOption() while validation has not been triggered yet
 * * configure validation by overwriting configureOptions(OptionsResolverInterface $resolver)
 *
 * * access configured options:
 *   * $this->getOption($name):                     will return the option if configured, throw exception if not
 *   * $this->getOptionOrDefault($name, $default):  will return the option if configured, default if not
 *   * $this->hasOption($name):                     will return true or false depending on configured options
 *
 * @author David Herrmann <office@web-emerge.com>
 */
trait ValidatedOptionsTrait
{
    /**
     * This holds the validated option values once validation has been triggered
     * by calling any of the getOption/hasOption() methods.
     *
     * @var array
     */
    protected $validatedOptions;

    /**
     * This holds options that can only be set in some context, so they cannot
     * be included in setOptions().
     *
     * @var array
     */
    protected $runtimeOptions = array();

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
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            throw new InvalidOptionException('Unknown option: '.$name);
        }

        return $this->validatedOptions[$name];
    }

    /**
     * Get the value of the given option, validated by the OptionsResolver.
     * Validation is triggered automatically when this method is called.
     *
     * Other than getOption() this method allows for a default value that will
     * be returned if the option has not been configured.
     *
     * Exceptions that may be thrown by the OptionsResolver itself:
     *
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the
     *                                   specified validation rules
     * @throws MissingOptionsException   If a required option is missing
     * @throws OptionDefinitionException If there is a cyclic dependency between
     *                                   lazy options and/or normalizers
     * @throws NoSuchOptionException     If a lazy option reads an unavailable option
     * @throws AccessException           If called from a lazy option or normalizer
     *
     * @param string      $name
     * @param string|null $default
     *
     * @return mixed
     */
    public function getOptionOrDefault($name, $default = null)
    {
        if (!$this->hasOption($name)) {
            return $default;
        }

        return $this->validatedOptions[$name];
    }

    /**
     * Check if the given option has been configured.
     *
     * Exceptions that may be thrown by the OptionsResolver itself:
     *
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
    public function hasOption($name)
    {
        $this->validateOptions();

        return array_key_exists($name, $this->validatedOptions);
    }

    /**
     * Validate all configured options if they have not been validated yet.
     */
    protected function validateOptions()
    {
        if (null === $this->validatedOptions) {
            $resolver = new OptionsResolver();
            $this->configureOptions($resolver);

            $options = array_merge($this->setOptions(), $this->runtimeOptions);
            $this->validatedOptions = $resolver->resolve($options);
        }
    }

    /**
     * Set raw option values right before validation. This can be used to chain
     * options in inheritance setups.
     *
     * @return array
     */
    protected function setOptions()
    {
        return array();
    }

    /**
     * Set any option value that cannot be included in setOptions().
     * This has to be called before validateOptions() is triggered, otherwise
     * a RuntimeException will be thrown.
     *
     * @throws \RuntimeException if options already have been validated.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    protected function setRuntimeOption($name, $value)
    {
        if (null !== $this->validatedOptions) {
            throw new \RuntimeException('Cannot set runtime options after options have been validated.');
        }
        $this->runtimeOptions[(string) $name] = $value;

        return $this;
    }

    /**
     * Set default options, set required options - whatever is needed.
     * This will be called during first access to any of the object-related methods.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }
}

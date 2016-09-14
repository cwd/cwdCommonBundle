<?php
namespace Cwd\CommonBundle\Tests\Options;

use Cwd\CommonBundle\Tests\Options\ValidatedOptionsTraitImpl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ValidatedOptionsTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptionCallsSetOptionsOnce()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
            ->setMethods(array(
                'setOptions',
                'configureOptions',
            ))
            ->getMock();

        $impl
            ->expects($this->once())
            ->method('setOptions')
            ->willReturn(array('required1' => 'bar'))
        ;
        $impl
            ->expects($this->once())
            ->method('configureOptions')
            ->with($this->callback(function (OptionsResolver $resolver) {
                $resolver->setRequired(array(
                    'required1',
                ));

                return $resolver instanceof OptionsResolver;
            }))
        ;

        $this->assertSame('bar', $impl->getOption('required1'));
        $this->assertSame('bar', $impl->getOption('required1'));
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testGetOptionCallsResolverAndFailsOnRequiredOption()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
                     ->setMethods(array(
                         'setOptions',
                         'configureOptions',
                     ))
                     ->getMock();

        $impl
            ->expects($this->once())
            ->method('setOptions')
            ->willReturn(array())
        ;
        $impl
            ->expects($this->once())
            ->method('configureOptions')
            ->with($this->callback(function (OptionsResolver $resolver) {
                $resolver->setRequired(array(
                    'required1',
                ));

                return $resolver instanceof OptionsResolver;
            }))
        ;

        $impl->getOption('required');
    }

    public function testConfigureOptionsIsCalledAndConfiguresResolver()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
            ->setMethods(array(
                'setOptions',
                'configureOptions',
            ))
            ->getMock();

        $impl
            ->expects($this->once())
            ->method('setOptions')
            ->willReturn(array('required1' => 'bar'))
        ;

        $impl
            ->expects($this->once())
            ->method('configureOptions')
            ->with($this->callback(function (OptionsResolver $resolver) {
                $resolver->setRequired(array(
                    'required1',
                ));
                $resolver->setDefaults(array(
                    'new_default' => 'baz',
                ));

                return $resolver instanceof OptionsResolver;
            }))
        ;

        $this->assertSame('bar', $impl->getOption('required1'));
        $this->assertSame('baz', $impl->getOption('new_default'));
    }

    /**
     * @expectedException MailingOwl\Service\Exception\InvalidOptionException
     */
    public function testGetOptionThrowsExceptionOnInvalidOption()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
            ->setMethods(array(
                'setOptions',
                'configureOptions',
            ))
            ->getMock();

        $impl
            ->expects($this->once())
            ->method('setOptions')
            ->willReturn(array('required1' => 'bar'))
        ;
        $impl
            ->expects($this->once())
            ->method('configureOptions')
            ->with($this->callback(function (OptionsResolver $resolver) {
                $resolver->setRequired(array(
                    'required1',
                ));

                return $resolver instanceof OptionsResolver;
            }))
        ;

        $impl->getOption('doesNotExist');
    }

    public function testGetOptionOrDefaultReturnsValueOrDefault()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
            ->setMethods(array(
                'setOptions',
                'configureOptions',
            ))
            ->getMock();
        $impl
            ->expects($this->once())
            ->method('setOptions')
            ->willReturn(array('required1' => 'bar'))
        ;
        $impl
            ->expects($this->once())
            ->method('configureOptions')
            ->with($this->callback(function (OptionsResolver $resolver) {
                $resolver->setRequired(array(
                    'required1',
                ));

                return $resolver instanceof OptionsResolver;
            }))
        ;

        $this->assertSame('bar', $impl->getOptionOrDefault('required1', 'baz'));
        $this->assertSame('baz', $impl->getOptionOrDefault('doesNotExist', 'baz'));
        $this->assertSame(null, $impl->getOptionOrDefault('doesNotExist'));
    }

    public function testWorksWithoutCallingSetOptionsIfNothingIsRequired()
    {
        $impl = $this->getMockBuilder(ValidatedOptionsTraitImpl::class)
                     ->setMethods(null)
                     ->getMock();

        $this->assertSame('baz', $impl->getOptionOrDefault('new_default', 'baz'));
    }
}

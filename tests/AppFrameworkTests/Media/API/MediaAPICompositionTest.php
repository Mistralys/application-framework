<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIMethodInterface;
use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait;
use Application\API\Traits\DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Traits\PaginatedAPIInterface;
use Application\API\Traits\PaginatedAPITrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait;
use Application\Media\API\Methods\AddMediaDocumentAPI;
use Application\Media\API\Methods\DeleteMediaDocumentAPI;
use Application\Media\API\Methods\GetMediaDocumentAPI;
use Application\Media\API\Methods\GetMediaDocumentsAPI;
use Application\Media\API\Methods\UpdateMediaDocumentAPI;
use Application\Media\Writer\MediaWriterException;
use AppFrameworkTestClasses\ApplicationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

/**
 * Covers the piece-structure half of the API Lego Rule for the media API
 * (`docs/agents/project-manifest/constraints.md`, "API Trait Composition
 * Model"):
 *
 * - no media or pagination trait `use`s another trait
 * - every media piece interface extends `APIMethodInterface` directly, with
 *   no inheritance between media pieces
 * - no media piece owns a validation code outside its own sub-range
 * - the removed writer-access piece and `MediaAPIException` do not exist
 *
 * It also covers the method-composition half (AC-21/the plan's composition
 * table) for all five media method classes: the two WP-007 introduces
 * ({@see GetMediaDocumentAPI}, {@see GetMediaDocumentsAPI}) and the three
 * WP-008 adds ({@see AddMediaDocumentAPI}, {@see UpdateMediaDocumentAPI},
 * {@see DeleteMediaDocumentAPI}).
 */
final class MediaAPICompositionTest extends ApplicationTestCase
{
    // region: No trait composes another trait

    /**
     * @return array<int, array{0: class-string}>
     */
    public static function mediaAndPaginationTraitProvider() : array
    {
        return array(
            array(MediaDocumentAPITrait::class),
            array(MediaMetadataAPITrait::class),
            array(MediaFileUploadAPITrait::class),
            array(PaginatedAPITrait::class),
        );
    }

    /**
     * @param class-string $traitName
     */
    #[DataProvider('mediaAndPaginationTraitProvider')]
    public function test_traitDeclaresNoTraitsOfItsOwn(string $traitName) : void
    {
        $reflection = new ReflectionClass($traitName);

        $this->assertSame(
            array(),
            $reflection->getTraitNames(),
            sprintf('[%s] must not use any other trait.', $traitName)
        );
    }

    // endregion

    // region: No inheritance between media piece interfaces

    /**
     * @return array<int, array{0: class-string}>
     */
    public static function mediaPieceInterfaceProvider() : array
    {
        return array(
            array(MediaDocumentAPIInterface::class),
            array(MediaMetadataAPIInterface::class),
            array(MediaFileUploadAPIInterface::class),
        );
    }

    /**
     * @param class-string $interfaceName
     */
    #[DataProvider('mediaPieceInterfaceProvider')]
    public function test_mediaPieceInterfaceExtendsAPIMethodInterfaceDirectly(string $interfaceName) : void
    {
        $reflection = new ReflectionClass($interfaceName);

        $parentNames = $reflection->getInterfaceNames();

        $this->assertContains(
            APIMethodInterface::class,
            $parentNames,
            sprintf('[%s] must extend APIMethodInterface.', $interfaceName)
        );

        $otherMediaPieces = array_diff(
            array(
                MediaDocumentAPIInterface::class,
                MediaMetadataAPIInterface::class,
                MediaFileUploadAPIInterface::class,
            ),
            array($interfaceName)
        );

        foreach($otherMediaPieces as $otherPiece) {
            $this->assertNotContains(
                $otherPiece,
                $parentNames,
                sprintf('[%s] must not extend the other media piece [%s].', $interfaceName, $otherPiece)
            );
        }
    }

    // endregion

    // region: One validation code range per piece

    public function test_mediaDocumentInterfaceOwnsOnlyItsSubRange() : void
    {
        $this->assertOwnIntConstantsInRange(MediaDocumentAPIInterface::class, 681200, 681209);
    }

    public function test_mediaMetadataInterfaceOwnsOnlyItsSubRange() : void
    {
        $this->assertOwnIntConstantsInRange(MediaMetadataAPIInterface::class, 681210, 681219);
    }

    public function test_mediaFileUploadInterfaceOwnsNoValidationCodes() : void
    {
        // The upload piece owns no codes of its own (design review Decision 5):
        // file validation failures are reported through MediaWriterException's
        // own 6810xx block instead.
        $this->assertOwnIntConstantsInRange(MediaFileUploadAPIInterface::class, 681200, 681299, allowEmpty: true, expectNone: true);
    }

    /**
     * @param class-string $interfaceName
     */
    private function assertOwnIntConstantsInRange(
        string $interfaceName,
        int $min,
        int $max,
        bool $allowEmpty=false,
        bool $expectNone=false
    ) : void
    {
        $reflection = new ReflectionClass($interfaceName);

        $ownIntConstants = array();

        foreach($reflection->getReflectionConstants() as $constant) {
            if($constant->getDeclaringClass()->getName() !== $interfaceName) {
                continue;
            }

            $value = $constant->getValue();

            // Only the VALIDATION_*/ERROR_*-style error code constants are
            // subject to the sub-range rule — plain integer configuration
            // constants (e.g. MAX_NAME_LENGTH) are not codes at all.
            if(is_int($value) && (str_starts_with($constant->getName(), 'VALIDATION_') || str_starts_with($constant->getName(), 'ERROR_'))) {
                $ownIntConstants[$constant->getName()] = $value;
            }
        }

        if($expectNone) {
            $this->assertSame(
                array(),
                $ownIntConstants,
                sprintf('[%s] must not declare any own integer (validation code) constants.', $interfaceName)
            );
            return;
        }

        if(!$allowEmpty) {
            $this->assertNotEmpty($ownIntConstants, sprintf('[%s] must declare at least one validation code.', $interfaceName));
        }

        foreach($ownIntConstants as $name => $value) {
            $this->assertGreaterThanOrEqual(
                $min,
                $value,
                sprintf('[%s::%s] (%s) must be >= %s.', $interfaceName, $name, $value, $min)
            );

            $this->assertLessThanOrEqual(
                $max,
                $value,
                sprintf('[%s::%s] (%s) must be <= %s.', $interfaceName, $name, $value, $max)
            );
        }
    }

    // endregion

    // region: The removed writer-access piece and MediaAPIException do not exist

    public function test_removedWriterPieceDoesNotExist() : void
    {
        $this->assertFalse(interface_exists('Application\Media\API\MediaWriterAPIInterface'));
        $this->assertFalse(trait_exists('Application\Media\API\MediaWriterAPITrait'));
        $this->assertFalse(class_exists('Application\Media\API\MediaAPIException'));
    }

    public function test_mediaWriterExceptionDeclaresNoCode681007() : void
    {
        $reflection = new ReflectionClass(MediaWriterException::class);

        $values = array_values($reflection->getConstants());

        $this->assertNotContains(681007, $values, 'MediaWriterException must not declare a constant with value 681007.');
    }

    // endregion

    // region: Method composition (the plan's Approach §4 table)

    /**
     * Each row: [method class, composition-table interfaces, matching traits].
     * `BaseAPIMethod` itself contributes `APIMethodInterface` and
     * `Application_Interfaces_Loggable` to every method (see
     * {@see getInterfacesAddedBySubclass()}) — these are the baseline that
     * is subtracted out on both sides of the comparison, not listed here.
     *
     * @return array<int, array{0: class-string, 1: class-string[], 2: class-string[]}>
     */
    public static function methodCompositionProvider() : array
    {
        return array(
            array(
                GetMediaDocumentAPI::class,
                array(
                    RequestRequestInterface::class,
                    JSONResponseWithExampleInterface::class,
                    APIKeyMethodInterface::class,
                    MediaDocumentAPIInterface::class,
                    MediaAPIResponseInterface::class,
                ),
                array(
                    RequestRequestTrait::class,
                    JSONResponseTrait::class,
                    JSONResponseWithExampleTrait::class,
                    APIKeyMethodTrait::class,
                    MediaDocumentAPITrait::class,
                ),
            ),
            array(
                GetMediaDocumentsAPI::class,
                array(
                    RequestRequestInterface::class,
                    JSONResponseWithExampleInterface::class,
                    APIKeyMethodInterface::class,
                    PaginatedAPIInterface::class,
                    MediaAPIResponseInterface::class,
                ),
                array(
                    RequestRequestTrait::class,
                    JSONResponseTrait::class,
                    JSONResponseWithExampleTrait::class,
                    APIKeyMethodTrait::class,
                    PaginatedAPITrait::class,
                ),
            ),
            array(
                AddMediaDocumentAPI::class,
                array(
                    JSONRequestInterface::class,
                    JSONResponseWithExampleInterface::class,
                    DryRunAPIInterface::class,
                    APIKeyMethodInterface::class,
                    MediaFileUploadAPIInterface::class,
                    MediaMetadataAPIInterface::class,
                    MediaAPIResponseInterface::class,
                ),
                array(
                    JSONRequestTrait::class,
                    JSONResponseTrait::class,
                    JSONResponseWithExampleTrait::class,
                    DryRunAPITrait::class,
                    APIKeyMethodTrait::class,
                    MediaFileUploadAPITrait::class,
                    MediaMetadataAPITrait::class,
                ),
            ),
            array(
                UpdateMediaDocumentAPI::class,
                array(
                    JSONRequestInterface::class,
                    JSONResponseWithExampleInterface::class,
                    DryRunAPIInterface::class,
                    APIKeyMethodInterface::class,
                    MediaDocumentAPIInterface::class,
                    MediaFileUploadAPIInterface::class,
                    MediaMetadataAPIInterface::class,
                    MediaAPIResponseInterface::class,
                ),
                array(
                    JSONRequestTrait::class,
                    JSONResponseTrait::class,
                    JSONResponseWithExampleTrait::class,
                    DryRunAPITrait::class,
                    APIKeyMethodTrait::class,
                    MediaDocumentAPITrait::class,
                    MediaFileUploadAPITrait::class,
                    MediaMetadataAPITrait::class,
                ),
            ),
            array(
                DeleteMediaDocumentAPI::class,
                array(
                    RequestRequestInterface::class,
                    JSONResponseWithExampleInterface::class,
                    DryRunAPIInterface::class,
                    APIKeyMethodInterface::class,
                    MediaDocumentAPIInterface::class,
                    MediaAPIResponseInterface::class,
                ),
                array(
                    RequestRequestTrait::class,
                    JSONResponseTrait::class,
                    JSONResponseWithExampleTrait::class,
                    DryRunAPITrait::class,
                    APIKeyMethodTrait::class,
                    MediaDocumentAPITrait::class,
                ),
            ),
        );
    }

    /**
     * @param class-string $methodClass
     * @param class-string[] $expectedInterfaces The composition-table row's piece interfaces.
     * @param class-string[] $expectedTraits The matching trait for every listed interface.
     */
    #[DataProvider('methodCompositionProvider')]
    public function test_methodImplementsExactlyItsCompositionTableRow(
        string $methodClass,
        array $expectedInterfaces,
        array $expectedTraits
    ) : void
    {
        $addedInterfaces = $this->getInterfacesAddedBySubclass($methodClass);
        $expectedAddedInterfaces = $this->getInterfacesAddedBySubclass($methodClass, $expectedInterfaces);

        sort($addedInterfaces);
        sort($expectedAddedInterfaces);

        $this->assertSame(
            $expectedAddedInterfaces,
            $addedInterfaces,
            sprintf('[%s] must implement exactly its composition-table row (plus their own parent interfaces).', $methodClass)
        );
    }

    /**
     * @param class-string $methodClass
     * @param class-string[] $expectedTraits
     */
    #[DataProvider('methodCompositionProvider')]
    public function test_methodDeclaresExactlyItsCompositionTableTraits(
        string $methodClass,
        array $expectedInterfaces,
        array $expectedTraits
    ) : void
    {
        $declaredTraits = (new ReflectionClass($methodClass))->getTraitNames();

        sort($declaredTraits);
        sort($expectedTraits);

        $this->assertSame(
            $expectedTraits,
            $declaredTraits,
            sprintf('[%s] must declare exactly the trait of every piece interface it implements, via its own `use`.', $methodClass)
        );
    }

    /**
     * Resolves the full transitive interface set a subclass of
     * `BaseAPIMethod` adds beyond what `BaseAPIMethod` itself always
     * contributes (`APIMethodInterface`, `Application_Interfaces_Loggable`
     * and their own parents).
     *
     * When `$interfaces` is omitted, resolves this for the real class
     * (`$methodClass`'s actual `getInterfaceNames()`). When given, resolves
     * it for a synthetic composition-table row instead — the union of each
     * listed interface with its own parent interfaces — so both sides of
     * the comparison are computed the same way and the baseline cancels out
     * identically.
     *
     * @param class-string $methodClass
     * @param class-string[]|null $interfaces
     * @return class-string[]
     */
    private function getInterfacesAddedBySubclass(string $methodClass, ?array $interfaces=null) : array
    {
        $base = (new ReflectionClass(BaseAPIMethod::class))->getInterfaceNames();

        if($interfaces === null) {
            $own = (new ReflectionClass($methodClass))->getInterfaceNames();
        } else {
            $own = array();
            foreach($interfaces as $interfaceName) {
                $own[] = $interfaceName;
                foreach((new ReflectionClass($interfaceName))->getInterfaceNames() as $parent) {
                    $own[] = $parent;
                }
            }
            $own = array_unique($own);
        }

        return array_values(array_diff($own, $base));
    }

    // endregion
}

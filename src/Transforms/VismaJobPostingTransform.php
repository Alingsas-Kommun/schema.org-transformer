<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms;

use SchemaTransformer\Interfaces\AbstractDataTransform;
use Spatie\SchemaOrg\Schema;
use SimpleXMLElement;


class VismaJobPostingTransform implements AbstractDataTransform
{
    /**
     * @param \SchemaTransformer\Interfaces\SanitizerInterface[] $sanitizers
     */
    public function __construct(private array $sanitizers) {}

    public function transform(array $data): array
    {
        $output = [];

        if (empty($data['content'])) {
            error_log("No XML content provided");
            return [];
        }

        $xmlString = $data['content'];

        try {
            $cleanXml = $this->sanitizeXML($xmlString);
            $xml = new SimpleXMLElement($cleanXml, LIBXML_NOCDATA | LIBXML_NOWARNING);

            $assignments = $xml->xpath('//Assignment');
        } catch (\Exception $e) {
            error_log("XML Parse Error: " . $e->getMessage());
            return [];
        }

        $assignments = $xml->xpath('//Assignment');
        error_log("Found " . count($assignments) . " assignments");

        foreach ($assignments as $assignment) {
            try {
                $localization = $assignment->Localization->AssignmentLoc[0] ?? null;
                if (!$localization) {
                    continue;
                }

                $departments = $localization->xpath('Departments/Department');

                $accountName = (string)$assignment->AccountName;
                $ownerDept = $localization->xpath('Departments/Department[@Type="Owner"]/Name');
                $ownerName = !empty($ownerDept) ? (string)$ownerDept[0] : '';


                $organizations = [
                    ['nameorgunit' => $accountName ?: ''],
                    ['nameorgunit' => $ownerName ?: '']
                ];

                [$org, $unit] = $this->normalizeArray($organizations, 2, ["nameorgunit" => ""]);

                $county = [
                    'name' => (string)($localization->County->Name ?? '')
                ];
                $municipality = [
                    'name' => (string)($localization->Municipality->Name ?? '')
                ];

                $jobPosting = Schema::jobPosting()
                    ->identifier((string)$assignment->RefNo)
                    ->totalJobOpenings((string)$assignment->NumberOfJobs)
                    ->title((string)$localization->AssignmentTitle)
                    ->description((string)$localization->WorkDescr)
                    ->jobStartDate((string)$localization->EmploymentStartDateDescr)
                    ->responsibilities((string)$localization->WorkDescr)
                    ->datePosted((string)$assignment->PublishStartDate)
                    ->experienceRequirements((string)$localization->WorkExperiencePrerequisite->Name)
                    ->employmentType((string)$localization->EmploymentGrade->Name ?? '')
                    ->workHours((string)$localization->EmploymentType->Name ?? '')
                    ->validThrough((string)$assignment->ApplicationEndDate);

                if (!empty($org['nameorgunit'])) {
                    $jobPosting->hiringOrganization(
                        Schema::organization()->name($org['nameorgunit'])
                    );
                }

                if (!empty($unit['nameorgunit'])) {
                    $organization = Schema::organization()->name($unit['nameorgunit']);

                    if (!empty($county['name']) || !empty($municipality['name'])) {
                        $address = Schema::postalAddress();
                        if (!empty($county['name'])) {
                            $address->addressRegion($county['name']);
                        }
                        if (!empty($municipality['name'])) {
                            $address->addressLocality($municipality['name']);
                        }
                        if (!empty($localization->Country->Name)) {
                            $address->addressCountry((string)$localization->Country->Name);
                        }
                        $organization->address($address);
                    }

                    $jobPosting->employmentUnit($organization);
                }


                $jobPosting->setProperty('@version', md5(json_encode($jobPosting->toArray())));
                $output[] = $jobPosting->toArray();
            } catch (\Exception $e) {
                error_log($e->getMessage());
                continue;
            }
        }

        error_log("Total job postings processed: " . count($output));
        return $output;
    }

    protected function normalizeArray(?array $in, int $length, array $fallback): array
    {
        if (empty($in) || !is_array($in)) {
            $in = [];
        }
        return array_pad($in, $length, $fallback);
    }

    private function sanitizeXML(string $xml): string
    {
        // Replace unescaped ampersands in URLs
        $xml = preg_replace('/&(?!(?:amp|quot|apos|lt|gt);)/', '&amp;', $xml);

        // Remove any invalid XML characters
        $xml = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $xml);

        return $xml;
    }
}

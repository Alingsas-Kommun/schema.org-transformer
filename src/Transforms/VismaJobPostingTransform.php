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

        // The XML string should be in $data['content'] or similar
        if (empty($data['content'])) {
            error_log("No XML content provided");
            return [];
        }

        $xmlString = $data['content'];

        try {
            // Clean XML and create SimpleXMLElement
            $cleanXml = $this->sanitizeXML($xmlString);
            $xml = new SimpleXMLElement($cleanXml, LIBXML_NOCDATA | LIBXML_NOWARNING);

            // Rest of your existing transform code...
            $assignments = $xml->xpath('//Assignment');
            // ... existing processing code ...

        } catch (\Exception $e) {
            error_log("XML Parse Error: " . $e->getMessage());
            return [];
        }

        // Get all Assignment nodes
        $assignments = $xml->xpath('//Assignment');
        error_log("Found " . count($assignments) . " assignments");

        foreach ($assignments as $assignment) {
            try {
                // Get localization data
                $localization = $assignment->Localization->AssignmentLoc[0] ?? null;
                if (!$localization) {
                    error_log("Missing localization for assignment " . $assignment->AssignmentId);
                    continue;
                }

                // Debug departments
                $departments = $localization->xpath('Departments/Department');
                error_log("Found " . count($departments) . " departments");
                foreach ($departments as $dept) {
                    error_log("Department: Type=" . $dept['Type'] . ", Name=" . $dept->Name);
                }

                // Get organization data with fallbacks
                $accountName = (string)$assignment->AccountName;
                $ownerDept = $localization->xpath('Departments/Department[@Type="Owner"]/Name');
                $ownerName = !empty($ownerDept) ? (string)$ownerDept[0] : '';

                error_log("AccountName: " . $accountName);
                error_log("OwnerName: " . $ownerName);

                $organizations = [
                    ['nameorgunit' => $accountName ?: ''],
                    ['nameorgunit' => $ownerName ?: '']
                ];

                [$org, $unit] = $this->normalizeArray($organizations, 2, ["nameorgunit" => ""]);

                // Get location data with validation
                $county = [
                    'name' => (string)($localization->County->Name ?? '')
                ];
                $municipality = [
                    'name' => (string)($localization->Municipality->Name ?? '')
                ];

                // Create job posting
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

                // Add organization data if available
                if (!empty($org['nameorgunit'])) {
                    $jobPosting->hiringOrganization(
                        Schema::organization()->name($org['nameorgunit'])
                    );
                }

                if (!empty($unit['nameorgunit'])) {
                    $organization = Schema::organization()->name($unit['nameorgunit']);

                    // Add address if location data is available
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

                // Add other fields...

                $jobPosting->setProperty('@version', md5(json_encode($jobPosting->toArray())));
                $output[] = $jobPosting->toArray();

                error_log("Successfully processed assignment " . $assignment->AssignmentId);
            } catch (\Exception $e) {
                error_log("Error processing assignment: " . $e->getMessage());
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

// $xmlString = file_get_contents('visma.xml');

// // Initialize transformer
// $transformer = new XMLJobPostingTransform([/* add your sanitizers here if needed */]);

// try {
//     // Transform the XML
//     print("Starting transformation");
//     $result = $transformer->transform($xmlString);

//     // Output the result
//     print_r($result, true);

//     // Write to file for inspection
//     file_put_contents('output.json', json_encode($result, JSON_PRETTY_PRINT));
//     echo "Transformation complete. Check output.json and debug.log\n";
// } catch (\Exception $e) {
//     print("Error during transformation: " . $e->getMessage());
//     echo "Error: " . $e->getMessage() . "\n";
// }

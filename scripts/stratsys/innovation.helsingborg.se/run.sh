#!/bin/bash
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

if [ -z ${STRATSYS_INNOVATION_PATH} ]; then
    echo "Missing env variable STRATSYS_INNOVATION_PATH"; exit 1
fi
if [ -z ${STRATSYS_INNOVATION_AUTH} ]; then
    echo "Missing env variable STRATSYS_INNOVATION_AUTH"; exit 1
fi
if [ -z ${STRATSYS_INNOVATION_CLIENTID} ]; then
    echo "Missing env variable STRATSYS_INNOVATION_CLIENTID"; exit 1
fi
if [ -z ${STRATSYS_INNOVATION_CLIENTSECRET} ]; then
    echo "Missing env variable STRATSYS_CLIENTSECRET"; exit 1
fi
if [ -z ${TYPESENSE_APIKEY} ]; then
    echo "Missing env variable TYPESENSE_APIKEY"; exit 1
fi
if [ -z ${TYPESENSE_BASE_PATH} ]; then
    echo "Missing env variable TYPESENSE_BASE_PATH"; exit 1
fi
which php
if [ $? -ne 0 ]; then
    echo "PHP command missing or not in path"; exit 1
fi
cd ${SCRIPT_DIR}

TMPFILE=$(mktemp)
TYPESENSE_PATH=${TYPESENSE_BASE_PATH}/collections/stratsys/documents

# Retreive and transform Stratsys export
php ../../../router.php \
    --source ${STRATSYS_INNOVATION_PATH} \
    --authpath ${STRATSYS_INNOVATION_AUTH} \
    --authclientid ${STRATSYS_INNOVATION_CLIENTID} \
    --authclientsecret ${STRATSYS_INNOVATION_CLIENTSECRET} \
    --authscope exportview.read \
    --transform stratsys \
    --outputformat jsonl \
    --output ${TMPFILE}

if [ $? -ne 0 ]; then
    echo "FAILED to transform request to file ${TMPFILE}"
else
    # Clear collection
    echo "Deleting documents"
    curl ${TYPESENSE_PATH}?filter_by=@type:Article -X DELETE -H "x-typesense-api-key: ${TYPESENSE_APIKEY}"

    if [ $? -ne 0 ]; then
        echo "FAILED to delete documents"
    fi

    # POST result to typesense
    echo "Creating documents"
    curl ${TYPESENSE_PATH}/import?action=create -X POST --data-binary @${TMPFILE} -H "Content-Type: text/plain" -H "x-typesense-api-key: ${TYPESENSE_APIKEY}"

    if [ $? -ne 0 ]; then
        echo "FAILED to upload document"
    fi
fi
# Remove temp file
rm -f ${TMPFILE}
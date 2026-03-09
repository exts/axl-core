<?php
namespace AxlCore\Conversation\Content;

enum ContentType
{
    case text;
    case textFile;
    case audio;
    case imageUrl;
    case imageFileId;
    case imageToBase64;
    case imageFileToBase64;
    case documentId;
    case documentToBase64;
    case documentFileToBase64;
}
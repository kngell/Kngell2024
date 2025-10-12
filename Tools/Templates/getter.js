module.exports = (prop) => {
  const name = prop.getName();
  const nameUcFirst = name.charAt(0).toUpperCase() + name.slice(1);
  const type = prop.getType();
  const nullable = prop.isNullable();

  const returnType = type ? (nullable ? `?${type}` : type) : "";
  const docReturnType = type ? (nullable ? `null|${type}` : type) : "mixed";

  return `/**
 * @return ${docReturnType}
 */
public function get${nameUcFirst}(): ${returnType}
{
    return $this->${name};
}
`;
};
